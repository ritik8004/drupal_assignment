<?php

namespace Drupal\acq_sku\AcquiaCommerce;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;

/**
 * Defines a base SKU Plugin. Can be used as a template for a new SKU type.
 */
abstract class SKUPluginBase implements SKUPluginInterface, FormInterface {

  /**
   * {@inheritdoc}
   *
   * If you need to alter the display of the whole entity, override this method.
   */
  public function build(array $build) {
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sku_base_form';
  }

  /**
   * {@inheritdoc}
   *
   * If you need more than one form in your SKU Type, separate out the forms
   * using form arguments. By default we fetch the SKU from the form state and
   * render the addToCartForm.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();

    if (empty($build_info['args'])) {
      return $this->addToCartForm($form, $form_state);
    }

    $sku = $build_info['args'][0];

    if (get_class($sku) == 'Drupal\acq_sku\Entity\SKU') {
      return $this->addToCartForm($form, $form_state, $build_info['args'][0]);
    }

    return $this->addToCartForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * If you need more than one form validation in your SKU Type, separate out
   * the form validation using form arguments.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->addToCartValidate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * If you need more than one form submission in your SKU Type, separate out
   * the form validation using form arguments.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->addToCartSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function addToCartForm(array $form, FormStateInterface $form_state, SKU $sku = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function addToCartValidate(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function addToCartSubmit(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function processImport($sku, array $product) {

  }

  /**
   * {@inheritdoc}
   */
  public function cartName(SKU $sku, array $cart, $asString = FALSE) {
    // For all configurable products we will have sku of simple variant only
    // in cart so we add a check if parent is available, process cartName of
    // that.
    if ($parent_sku = $this->getParentSku($sku)) {
      $plugin_manager = \Drupal::service('plugin.manager.sku');
      $plugin = $plugin_manager->pluginInstanceFromType($parent_sku->bundle());
      if (method_exists($plugin, 'cartName')) {
        return $plugin->cartName($sku, $cart, $asString);
      }
    }

    $cartName = $sku->label();
    if (!$asString) {
      $display_node = $this->getDisplayNode($sku);

      // If node object.
      if ($display_node instanceof Node) {
        $url = $display_node->toUrl();
        $link = Link::fromTextAndUrl($cartName, $url);
        $cartName = $link->toRenderable();
      }
      else {
        \Drupal::logger('acq_sku')->info('Parent product for the sku: @sku seems to be unavailable.', [
          '@sku' => $sku->getSku(),
        ]);
      }
    }

    return $cartName;
  }

  /**
   * Get parent of current product.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Current product.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   Parent product or null if not found.
   */
  public function getParentSku(SKU $sku) {
    $static = &drupal_static(__FUNCTION__, []);

    $langcode = $sku->language()->getId();
    $sku_string = $sku->getSku();

    if (isset($static[$langcode], $static[$langcode][$sku_string])) {
      return $static[$langcode][$sku_string];
    }

    // Initialise with empty value.
    $static[$langcode][$sku_string] = NULL;

    $query = \Drupal::database()->select('acq_sku_field_data', 'acq_sku');
    $query->addField('acq_sku', 'sku');
    $query->join('acq_sku__field_configured_skus', 'child_sku', 'acq_sku.id = child_sku.entity_id');
    $query->condition('child_sku.field_configured_skus_value', $sku_string);

    $parent_skus = array_keys($query->execute()->fetchAllAssoc('sku'));

    if (empty($parent_skus)) {
      return NULL;
    }

    if (count($parent_skus) > 1) {
      \Drupal::logger('acq_sku')->warning(
        'Multiple parents found for SKU: @sku, parents: @parents',
        [
          '@parents' => implode(',', $parent_skus),
          '@sku' => $sku_string,
        ]
      );
    }

    foreach ($parent_skus as $parent_sku) {
      $parent = SKU::loadFromSku($parent_sku, $langcode);
      if ($parent instanceof SKU) {
        $node = $this->getDisplayNode($parent, FALSE, FALSE);

        if ($node instanceof Node) {
          $static[$langcode][$sku_string] = $parent;
          break;
        }
      }
    }

    return $static[$langcode][$sku_string];
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayNode(SKU $sku, $check_parent = TRUE, $create_translation = FALSE) {
    $static = &drupal_static(__FUNCTION__, []);

    $langcode = $sku->language()->getId();
    $sku_string = $sku->getSku();

    // Do not use static cache during sync when create translation flag is set
    // to true.
    if (!($create_translation) && isset($static[$langcode], $static[$langcode][$sku_string])) {
      return $static[$langcode][$sku_string];
    }

    // Initialise with empty value.
    $static[$langcode][$sku_string] = NULL;

    if ($check_parent) {
      if ($parent_sku = $this->getParentSku($sku)) {
        $sku = $parent_sku;
      }
    }

    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'acq_product');
    $query->condition('field_skus', $sku->getSku());

    $query->range(0, 1);

    $result = $query->execute();

    if (empty($result)) {
      return NULL;
    }

    $nid = reset($result);
    $node = Node::load($nid);

    // Check language checks if site is in multilingual mode.
    if (\Drupal::languageManager()->isMultilingual()) {
      // If language of SKU and node are the same, we return the node.
      if ($node->language()->getId() == $langcode) {
        return $node;
      }

      // If node has translation, we return the translation.
      if ($node->hasTranslation($langcode)) {
        return $node->getTranslation($langcode);
      }

      // If translation not available and create_translation flag is true.
      if ($create_translation) {
        return $node->addTranslation($langcode);
      }

      // Just log the message and continue.
      // Don't want to show any fatal error anywhere.
      \Drupal::logger('acq_sku')->warning('Node translation not found of @sku for @langcode', [
        '@sku' => $sku->getSku(),
        '@langcode' => $langcode,
      ]);
    }

    $static[$langcode][$sku_string] = $node;

    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessedStock(SKU $sku, $reset = FALSE) {
    $stock = &drupal_static('stock_static_cache', []);

    if (!$reset && isset($stock[$sku->getSku()])) {
      return $stock[$sku->getSku()];
    }

    $stock[$sku->getSku()] = (int) $this->getStock($sku, $reset);

    return $stock[$sku->getSku()];
  }

  /**
   * Returns the stock for the given sku.
   *
   * @param string $sku
   *   SKU code of the product.
   * @param bool $reset
   *   Flag to mention if we should always try to get fresh value.
   *
   * @return array|mixed
   *   Available stock quantity.
   */
  protected function getStock($sku, $reset = FALSE) {
    $stock_mode = \Drupal::config('acq_sku.settings')->get('stock_mode');
    $sku_string = ($sku instanceof SKU) ? $sku->getSku() : $sku;

    if (!$reset) {
      // Return from Entity field in push mode.
      if ($stock_mode == 'push') {
        if ($sku instanceof SKU) {
          $stock = $sku->get('stock')->getString();
        }
        else {
          $stock = \Drupal::database()->select('acq_sku_field_data', 'asfd')
            ->fields('asfd', ['stock'])
            ->condition('asfd.sku', $sku_string)
            ->execute()
            ->fetchField();
        }

        // Fallback to pull mode if no value available for the SKU.
        if (!($stock === '' || $stock === NULL)) {
          return (int) $stock;
        }
      }
      // Return from Cache in Pull mode.
      else {
        // Cache id.
        $cid = 'stock:' . $sku_string;

        $cache = \Drupal::cache('stock')->get($cid);

        if (!empty($cache)) {
          return (int) $cache->data;
        }
      }
    }

    // Either reset is requested or we dont have value in attribute or we dont
    // have value in cache, we will use the API to get fresh value now.
    $stock = NULL;

    /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
    $api_wrapper = \Drupal::service('acq_commerce.api');

    try {
      // Get the stock.
      $stock_info = $api_wrapper->skuStockCheck($sku_string);
    }
    catch (\Exception $e) {
      // Log the stock error, do not throw error if stock info is missing.
      \Drupal::logger('acq_sku')->warning('Unable to get the stock for @sku : @message', [
        '@sku' => $sku_string,
        '@message' => $e->getMessage(),
      ]);

      // We will cache this also for sometime to reduce load.
      $stock_info['is_in_stock'] = FALSE;
    }

    // Magento uses additional flag as well for out of stock.
    if (isset($stock_info['is_in_stock']) && empty($stock_info['is_in_stock'])) {
      $stock_info['quantity'] = 0;
    }

    $stock = (int) $stock_info['quantity'];

    // Save the value in SKU if we came here as fallback of push mode.
    if ($stock_mode == 'push') {
      if (!$sku instanceof SKU) {
        $sku = SKU::loadFromSku($sku_string);
      }

      $sku->get('stock')->setValue($stock);
      $sku->save();
    }
    // Save the value in cache if we are in pull mode.
    // If cache multiplier is zero we don't cache the stock.
    elseif ($cache_multiplier = \Drupal::config('acq_sku.settings')->get('stock_cache_multiplier')) {
      $default_cache_lifetime = $stock ? $stock * $cache_multiplier : $cache_multiplier;
      $max_cache_lifetime = \Drupal::config('acq_sku.settings')->get('stock_cache_max_lifetime');

      // Calculate the timestamp when we want the cache to expire.
      $stock_cache_lifetime = min($default_cache_lifetime, $max_cache_lifetime);
      $expire = $stock_cache_lifetime + \Drupal::time()->getRequestTime();

      // Set the stock in cache.
      \Drupal::cache('stock')->set($cid, $stock, $expire);
    }

    return $stock;
  }

}
