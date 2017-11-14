<?php

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_sku\AcquiaCommerce\SKUPluginBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Link;
use Drupal\acq_sku\AddToCartErrorEvent;
use Drupal\acq_sku\ProductOptionsManager;

/**
 * Defines the configurable SKU type.
 *
 * @SKUType(
 *   id = "configurable",
 *   label = @Translation("Configurable SKU"),
 *   description = @Translation("Configurable SKU for picking out a product."),
 * )
 */
class Configurable extends SKUPluginBase {

  /**
   * {@inheritdoc}
   */
  public function addToCartForm(array $form, FormStateInterface $form_state, SKU $sku = NULL) {
    if (empty($sku)) {
      return $form;
    }

    $form_state->set('tree', $this->deriveProductTree($sku));

    $form['ajax'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['configurable_ajax'],
      ],
    ];

    $form['ajax']['configurables'] = [
      '#tree' => TRUE,
    ];

    $configurables = unserialize($sku->field_configurable_attributes->getString());
    $configurable_form_settings = \Drupal::service('config.factory')->get('acq_sku.configurable_form_settings');
    $configurable_weights = $configurable_form_settings->get('attribute_weights');

    foreach ($configurables as $configurable) {
      $attribute_code = $configurable['code'];

      $options = [];
      $sorted_options = [];

      foreach ($configurable['values'] as $value) {
        $options[$value['value_id']] = $value['label'];
      }

      // Sort the options.
      if (!empty($options)) {
        $query = \Drupal::database()->select('taxonomy_term_field_data', 'ttfd');
        $query->fields('ttfd', ['tid', 'weight']);
        $query->join('taxonomy_term__field_sku_attribute_code', 'ttfsac', 'ttfsac.entity_id = ttfd.tid');
        $query->join('taxonomy_term__field_sku_option_id', 'ttfsoi', 'ttfsoi.entity_id = ttfd.tid');
        $query->fields('ttfsoi', ['field_sku_option_id_value']);
        $query->condition('ttfd.vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
        $query->condition('ttfsac.field_sku_attribute_code_value', $attribute_code);
        $query->condition('ttfsoi.field_sku_option_id_value', array_keys($options), 'IN');
        $query->distinct();
        $query->orderBy('weight', 'ASC');
        $tids = $query->execute()->fetchAllAssoc('tid');

        foreach ($tids as $tid => $values) {
          $sorted_options[$values->field_sku_option_id_value] = $options[$values->field_sku_option_id_value];
        }

        $form['ajax']['configurables'][$attribute_code] = [
          '#type' => 'select',
          '#title' => $configurable['label'],
          '#options' => $sorted_options,
          '#weight' => $configurable_weights[$attribute_code],
          '#required' => TRUE,
          '#ajax' => [
            'callback' => [$this, 'configurableAjaxCallback'],
            'progress' => [
              'type' => 'throbber',
              'message' => NULL,
            ],
            'wrapper' => 'configurable_ajax',
          ],
        ];
      }
      else {
        \Drupal::logger('acq_sku')->info('Product with sku: @sku seems to be configurable without any config options.', ['@sku' => $sku->getSku()]);
      }
    }

    $form['sku_id'] = [
      '#type' => 'hidden',
      '#value' => $sku->id(),
    ];

    $form['quantity'] = [
      '#title' => t('Quantity'),
      '#type' => 'number',
      '#default_value' => 1,
      '#access' => $configurable_form_settings->get('show_quantity'),
      '#required' => TRUE,
      '#size' => 2,
    ];

    $form['add_to_cart'] = [
      '#type' => 'submit',
      '#value' => t('Add to cart'),
    ];

    return $form;
  }

  /**
   * Updates the form based on selections.
   *
   * @param array $form
   *   Array of form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Values of form.
   *
   * @return array
   *   Array with dynamic parts of the form.
   */
  public static function configurableAjaxCallback(array $form, FormStateInterface $form_state) {
    $dynamic_parts = &$form['ajax'];

    $configurables = $form_state->getValue('configurables');
    $tree = $form_state->get('tree');
    $tree_pointer = &$tree['options'];

    foreach ($configurables as $key => $value) {
      if (empty($value)) {
        continue;
      }

      // Move the tree pointer if the selection is valid.
      if (isset($tree_pointer["$key:$value"])) {
        $tree_pointer = &$tree_pointer["$key:$value"];
      }
    }

    if ($tree_pointer instanceof SKU) {
      $plugin = $tree_pointer->getPluginInstance();

      $view_builder = \Drupal::entityTypeManager()
        ->getViewBuilder('acq_sku');

      $view = $view_builder
        ->view($tree_pointer);

      // Block add to cart render because Form API won't allow AJAX Formception.
      $view['#no_add_to_cart'] = TRUE;

      $dynamic_parts['add_to_cart'] = [
        'entity_render' => ['#markup' => render($view)],
      ];
    }
    else {
      $available_config = $tree_pointer['#available_config'];

      foreach ($available_config as $key => $config) {
        $options = [
          '' => $dynamic_parts['configurables']['color']['#options'][''],
        ];

        foreach ($config['values'] as $value) {
          $options[$value['value_id']] = $value['label'];
        }

        $dynamic_parts['configurables'][$key]['#options'] = $options;
      }
    }

    return $dynamic_parts;
  }

  /**
   * {@inheritdoc}
   */
  public function addToCartSubmit(array &$form, FormStateInterface $form_state) {
    $quantity = $form_state->getValue('quantity');
    $configurables = $form_state->getValue('configurables');
    $tree = $form_state->get('tree');
    $tree_pointer = &$tree['options'];

    foreach ($configurables as $key => $value) {
      if (empty($value)) {
        continue;
      }

      // Move the tree pointer if the selection is valid.
      if (isset($tree_pointer["$key:$value"])) {
        $tree_pointer = &$tree_pointer["$key:$value"];
      }
    }

    if ($tree_pointer instanceof SKU) {
      $cart = \Drupal::service('acq_cart.cart_storage')->getCart();

      // Cart here can be empty only if APIs aren't working.
      // Call above is to create cart if empty, we except a new or old cart here
      // and it can be empty if server is not working or in maintenance mode.
      if (empty($cart)) {
        $e = new \Exception(acq_commerce_api_down_global_error_message(), APIWrapper::API_DOWN_ERROR_CODE);

        // Dispatch event so action can be taken.
        $dispatcher = \Drupal::service('event_dispatcher');
        $event = new AddToCartErrorEvent($e);
        $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
        return;
      }

      $options = [];
      $label_parts = [];
      $configurables_form = $form['ajax']['configurables'];

      foreach ($configurables as $option_name => $option_value) {
        $options[] = [
          'option_id' => $tree['configurables'][$option_name]['attribute_id'],
          'option_value' => $option_value,
        ];

        $label_parts[] = sprintf(
          '%s: %s',
          $tree['configurables'][$option_name]['label'],
          $configurables_form[$option_name]['#options'][$option_value]
        );
      }

      $label = sprintf(
        '%s (%s)',
        $tree['parent']->label(),
        implode(', ', $label_parts)
      );

      drupal_set_message(
        t('Added @quantity of @name to the cart.',
          [
            '@quantity' => $quantity,
            '@name' => $label,
          ]
      ));

      $cart->addRawItemToCart([
        'name' => $label,
        'sku' => $tree['parent']->getSKU(),
        'qty' => $quantity,
        'options' => [
          'configurable_item_options' => $options,
        ],
      ]);

      // Add child SKU to form state to allow other modules to use it.
      $form_state->setTemporaryValue('child_sku', $tree_pointer->getSKU());

      try {
        \Drupal::service('acq_cart.cart_storage')->updateCart();
      }
      catch (\Exception $e) {
        // Clear stock cache.
        $stock_cid = acq_sku_get_stock_cache_id($tree_pointer);
        \Drupal::cache('stock')->invalidate($stock_cid);

        // Clear product and forms related to sku.
        Cache::invalidateTags(['acq_sku:' . $tree_pointer->id()]);

        // Dispatch event so action can be taken.
        $dispatcher = \Drupal::service('event_dispatcher');
        $event = new AddToCartErrorEvent($e);
        $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
      }
    }
    else {
      drupal_set_message(t('The current selection does not appear to be valid.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processImport($sku, array $product) {
    $sku->field_configurable_attributes->value =
      serialize($product['extension']['configurable_product_options']);

    $skus = [];

    $price = NULL;
    $max_price = 0;
    $min_price = NULL;

    foreach ($product['extension']['configurable_product_links'] as $product) {
      $sku_name = $product['sku'];

      $skus[] = ['value' => $sku_name];

      if (empty($sku_entity)) {
        continue;
      }

      $price = (float) $sku_entity->price->first()->value;

      if ($price < $min_price || $min_price === NULL) {
        $min_price = $price;
      }

      if ($price > $max_price) {
        $max_price = $price;
      }
    }

    if ($max_price != $min_price) {
      $price = t('From @min to @max');
    }
    else {
      $price = $max_price;
    }

    $sku->price->value = $price;
    $sku->get('field_configured_skus')->setValue($skus);
  }

  /**
   * Builds a display tree.
   *
   * Helps to determine which products belong to which combination of
   * configurables.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Object of SKU.
   *
   * @return array
   *   Configurables tree.
   */
  public function deriveProductTree(SKU $sku) {
    static $cache = [];

    if (isset($cache[$sku->language()->getId()], $cache[$sku->language()->getId()][$sku->id()])) {
      return $cache[$sku->language()->getId()][$sku->id()];
    }

    $tree = ['parent' => $sku];

    foreach ($sku->field_configured_skus as $child_sku) {
      $child_sku = SKU::loadFromSku($child_sku->getString());
      if (!empty($child_sku)) {
        $tree['products'][$child_sku->getSKU()] = $child_sku;
      }
    }

    $configurables = unserialize(
      $sku->field_configurable_attributes->getString()
    );

    $tree['configurables'] = [];
    foreach ($configurables as $configurable) {
      $tree['configurables'][$configurable['code']] = $configurable;
    }

    $configurable_weights = \Drupal::service('config.factory')->get('acq_sku.configurable_form_settings')->get('attribute_weights');

    // Sort configurables based on the config.
    uasort($tree['configurables'], function ($a, $b) use ($configurable_weights) {
      return $configurable_weights[$a['code']] - $configurable_weights[$b['code']];
    });

    $tree['options'] = Configurable::recursiveConfigurableTree(
      $tree,
      $tree['configurables']
    );

    $cache[$sku->language()->getId()][$sku->id()] = $tree;

    return $tree;
  }

  /**
   * Creates subtrees based on available config.
   *
   * @param array $tree
   *   Tree of products.
   * @param array $available_config
   *   Available configs.
   * @param array $current_config
   *   Config of current product.
   *
   * @return array
   *   Subtree.
   */
  public static function recursiveConfigurableTree(array &$tree, array $available_config, array $current_config = []) {
    $subtree = ['#available_config' => $available_config];

    foreach ($available_config as $id => $config) {
      $subtree_available_config = $available_config;
      unset($subtree_available_config[$id]);

      foreach ($config['values'] as $option) {
        $value = $option['value_id'];
        $subtree_current_config = array_merge($current_config, [$id => $value]);

        if (count($subtree_available_config) > 0) {
          $subtree["$id:$value"] = Configurable::recursiveConfigurableTree(
            $tree,
            $subtree_available_config,
            $subtree_current_config
          );
        }
        else {
          $subtree["$id:$value"] = Configurable::findProductInTreeWithConfig(
            $tree,
            $subtree_current_config
          );
        }
      }
    }

    return $subtree;
  }

  /**
   * Finds product in tree base on config.
   *
   * @param array $tree
   *   The whole configurable tree.
   * @param array $config
   *   Config for the product.
   *
   * @return \Drupal\acq_sku\Entity\SKU
   *   Reference to SKU in existing tree.
   */
  public static function &findProductInTreeWithConfig(array &$tree, array $config) {
    $child_skus = array_keys($tree['products']);
    $query = \Drupal::database()->select('acq_sku_field_data', 'acq_sku');

    $query->addField('acq_sku', 'sku');

    if (!empty($child_skus)) {
      $query->condition('sku', $child_skus, 'IN');
    }

    foreach ($config as $key => $value) {
      $query->join('acq_sku__attributes', $key, "acq_sku.id = $key.entity_id");
      $query->condition("$key.attributes_key", $key);
      $query->condition("$key.attributes_value", $value);
    }

    $sku = $query->execute()->fetchField();
    return $tree['products'][$sku];
  }

  /**
   * Get attribute value from key-value field.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The object of product.
   * @param string $key
   *   Name of attribute.
   *
   * @return string|null
   *   Value of field or null if empty.
   */
  public function getAttributeValue(SKU $sku, $key) {
    $query = \Drupal::database()->select('acq_sku__attributes', 'acq_sku__attributes');
    $query->addField('acq_sku__attributes', 'attributes_value');
    $query->condition("acq_sku__attributes.entity_id", $sku->id());
    $query->condition("acq_sku__attributes.attributes_key", $key);
    return $query->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function cartName($sku, array $cart) {
    $parent_sku = $this->getParentSku($sku);

    if (empty($parent_sku)) {
      return $sku->label();
    }

    $configurables = unserialize(
      $parent_sku->field_configurable_attributes->getString()
    );

    $label_parts = [];

    foreach ($configurables as $configurable) {
      $key = $configurable['code'];
      $attribute_value = $this->getAttributeValue($sku, $key);
      $label = $configurable['label'];

      foreach ($configurable['values'] as $value) {
        if ($attribute_value == $value['value_id']) {
          $label_parts[] = sprintf(
            '%s: %s',
            $label,
            $value['label']
          );
        }
      }
    }

    $label = sprintf(
      '%s (%s)',
      $cart['name'],
      implode(', ', $label_parts)
    );

    $display_node = $this->getDisplayNode($parent_sku);
    $url = $display_node->toUrl();
    $link = Link::fromTextAndUrl($label, $url)->toRenderable();
    return render($link);
  }

}
