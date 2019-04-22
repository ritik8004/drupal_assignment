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

    $parent_skus = array_values($this->getParentSkus($sku_string));

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

    // Key to fetch display node of the sku from static cache based on whether
    // parent sku needs to consider or not.
    $check_parent_key = (int) $check_parent;

    // Do not use static cache during sync when create translation flag is set
    // to true.
    if (!$create_translation && isset($static[$langcode], $static[$langcode][$sku_string], $static[$langcode][$sku_string][$check_parent_key])) {
      return $static[$langcode][$sku_string][$check_parent_key];
    }

    // Initialise with empty value.
    $static[$langcode][$sku_string][$check_parent_key] = NULL;

    if ($check_parent) {
      if ($parent_sku = $this->getParentSku($sku)) {
        $sku = $parent_sku;
      }
    }

    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'acq_product');
    $query->condition('field_skus', $sku->getSku());
    $query->addTag('get_display_node_for_sku');

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
        // Do nothing as we just using the same node object for static cache.
      }
      elseif ($node->hasTranslation($langcode)) {
        // If node has translation, we return the translation.
        $node = $node->getTranslation($langcode);
      }
      elseif ($create_translation) {
        // If translation not available and create_translation flag is true.
        $node = $node->addTranslation($langcode);
      }
      else {
        // Just log the message and continue.
        // Don't want to show any fatal error anywhere.
        \Drupal::logger('acq_sku')->warning('Node translation not found of @sku for @langcode', [
          '@sku' => $sku->getSku(),
          '@langcode' => $langcode,
        ]);
      }
    }

    $static[$langcode][$sku_string][$check_parent_key] = $node;

    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function isProductInStock(SKU $sku) {
    $static = &drupal_static(self::class . '_' . __FUNCTION__, []);

    $sku_string = $sku->getSku();

    if (isset($static[$sku_string])) {
      return $static[$sku_string];
    }

    $static[$sku_string] = $this->getStockManager()->isProductInStock($sku);

    return $static[$sku_string];
  }

  /**
   * {@inheritdoc}
   */
  public function getStock($sku) {
    $static = &drupal_static(self::class . '_' . __FUNCTION__, []);

    $sku_string = ($sku instanceof SKU) ? $sku->getSku() : $sku;

    if (isset($static[$sku_string])) {
      return $static[$sku_string];
    }

    $static[$sku_string] = $this->getStockManager()->getStockQuantity($sku_string);

    return $static[$sku_string];
  }

  /**
   * {@inheritdoc}
   */
  public function refreshStock(SKU $sku) {
    $this->getStockManager()->refreshStock($sku->getSku());
  }

  /**
   * Get stock manager service instance.
   *
   * @return \Drupal\acq_sku\StockManager
   *   Stock Manager service.
   */
  protected function getStockManager() {
    static $manager;

    if (!isset($manager)) {
      /** @var \Drupal\acq_sku\StockManager $manager */
      $manager = \Drupal::service('acq_sku.stock_manager');
    }

    return $manager;
  }

  /**
   * Get parent skus of given sku.
   *
   * @param string $sku
   *   SKU string.
   *
   * @return array
   *   Parent skus.
   */
  public function getParentSkus(string $sku) {
    $query = \Drupal::database()->select('acq_sku_field_data', 'acq_sku');
    $query->addField('acq_sku', 'id');
    $query->addField('acq_sku', 'sku');
    $query->join('acq_sku__field_configured_skus', 'child_sku', 'acq_sku.id = child_sku.entity_id');
    $query->condition('child_sku.field_configured_skus_value', $sku);
    return $query->execute()->fetchAllKeyed();
  }

}
