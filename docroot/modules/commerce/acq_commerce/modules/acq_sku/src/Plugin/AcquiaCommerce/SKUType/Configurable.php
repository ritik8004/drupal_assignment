<?php

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_sku\AcquiaCommerce\SKUPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Link;
use Drupal\acq_sku\AddToCartErrorEvent;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\node\Entity\Node;

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

    /** @var \Drupal\acq_sku\CartFormHelper $helper */
    $helper = \Drupal::service('acq_sku.cart_form_helper');

    $configurable_weights = $helper->getConfigurableAttributeWeights(
      $sku->get('attribute_set')->getString()
    );

    // Sort configurables based on the config.
    uasort($configurables, function ($a, $b) use ($configurable_weights) {
      // We may keep getting new configurable options not defined in config.
      // Use default values for them and keep their sequence as is.
      // Still move the ones defined in our config as per weight in config.
      $a = $configurable_weights[$a['code']] ?? -50;
      $b = $configurable_weights[$b['code']] ?? 50;
      return $a - $b;
    });

    foreach ($configurables as $configurable) {
      $attribute_code = $configurable['code'];

      $options = [];

      foreach ($configurable['values'] as $value) {
        $options[$value['value_id']] = $value['label'];
      }

      // Sort the options.
      if (!empty($options)) {
        $sorted_options = $options;

        if ($helper->isAttributeSortable($attribute_code)) {
          $sorted_options = self::sortConfigOptions($options, $attribute_code);
        }

        $form['ajax']['configurables'][$attribute_code] = [
          '#type' => 'select',
          '#title' => $configurable['label'],
          '#options' => $sorted_options,
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
      '#access' => $helper->showQuantity(),
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
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('acq_sku');

      $view = $view_builder->view($tree_pointer);

      // Block add to cart render because Form API won't allow AJAX Formception.
      $view['#no_add_to_cart'] = TRUE;

      $dynamic_parts['add_to_cart'] = [
        'entity_render' => ['#markup' => render($view)],
      ];
    }
    else {
      $available_config = $tree_pointer['#available_config'];

      /** @var \Drupal\acq_sku\CartFormHelper $helper */
      $helper = \Drupal::service('acq_sku.cart_form_helper');

      foreach ($available_config as $key => $config) {
        $options = [
          '' => $dynamic_parts['configurables'][$key]['#options'][''],
        ];

        foreach ($config['values'] as $value) {
          $options[$value['value_id']] = $value['label'];
        }

        // Use this in case the attribute is not sortable as per the config.
        $sorted_options = $options;

        // Sort config options before pushing them to the select list based on
        // the config.
        if ($helper->isAttributeSortable($key)) {
          // Make sure the first element in the list is the empty option.
          $sorted_options = [
            '' => $dynamic_parts['configurables'][$key]['#options'][''],
          ];
          $sorted_options += self::sortConfigOptions($options, $key);
        }

        $dynamic_parts['configurables'][$key]['#options'] = $sorted_options;
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
      /** @var \Drupal\acq_cart\Cart $cart */
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

      // Check if item already in cart.
      // @TODO: This needs to be fixed further to handle multiple parent
      // products for a child SKU. To be done as part of CORE-7003.
      if ($cart->hasItem($tree_pointer->getSku())) {
        $cart->addItemToCart($tree_pointer->getSku(), $quantity);
      }
      else {
        $cart->addRawItemToCart([
          'name' => $label,
          'sku' => $tree['parent']->getSKU(),
          'qty' => $quantity,
          'options' => [
            'configurable_item_options' => $options,
          ],
        ]);
      }

      // Add child SKU to form state to allow other modules to use it.
      $form_state->setTemporaryValue('child_sku', $tree_pointer->getSKU());

      try {
        \Drupal::service('acq_cart.cart_storage')->updateCart();
      }
      catch (\Exception $e) {
        // Clear stock cache.
        $tree_pointer->clearStockCache();

        // Dispatch event so action can be taken.
        $dispatcher = \Drupal::service('event_dispatcher');
        $event = new AddToCartErrorEvent($e);
        $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
      }
    }
    else {
      $message = t('The current selection does not appear to be valid.');
      drupal_set_message($message);
      // Dispatch event so action can be taken.
      $dispatcher = \Drupal::service('event_dispatcher');
      $exception = new \Exception($message);
      $event = new AddToCartErrorEvent($exception);
      $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
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

    $tree = [
      'parent' => $sku,
      'products' => self::getChildren($sku),
      'combinations' => [],
    ];

    $configurables = unserialize(
      $sku->get('field_configurable_attributes')->getString()
    );

    $tree['configurables'] = [];
    foreach ($configurables ?? [] as $configurable) {
      $tree['configurables'][$configurable['code']] = $configurable;
    }

    $configurable_codes = array_keys($tree['configurables']);

    foreach ($tree['products'] ?? [] as $sku_code => $sku_entity) {
      $attributes = $sku_entity->get('attributes')->getValue();
      $attributes = array_column($attributes, 'value', 'key');
      foreach ($configurable_codes as $code) {
        $value = $attributes[$code] ?? '';

        if (empty($value)) {
          continue;
        }

        $tree['combinations']['by_sku'][$sku_code][$code] = $value;
      }
    }

    /** @var \Drupal\acq_sku\CartFormHelper $helper */
    $helper = \Drupal::service('acq_sku.cart_form_helper');

    $configurable_weights = $helper->getConfigurableAttributeWeights(
      $sku->get('attribute_set')->getString()
    );

    // Sort configurables based on the config.
    uasort($tree['configurables'], function ($a, $b) use ($configurable_weights) {
      // We may keep getting new configurable options not defined in config.
      // Use default values for them and keep their sequence as is.
      // Still move the ones defined in our config as per weight in config.
      $a = $configurable_weights[$a['code']] ?? -50;
      $b = $configurable_weights[$b['code']] ?? 50;
      return $a - $b;
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
  public static function findProductInTreeWithConfig(array $tree, array $config) {
    if (isset($tree['products'])) {
      $attributes = [];
      foreach ($config as $key => $value) {
        $attributes[$key] = $value;
      }

      foreach ($tree['combinations']['by_sku'] ?? [] as $sku => $sku_attributes) {
        if (count(array_intersect_assoc($sku_attributes, $attributes)) === count($sku_attributes)) {
          return $tree['products'][$sku];
        }
      }
    }

    return NULL;
  }

  /**
   * Get attribute value from key-value field.
   *
   * @param int $sku_id
   *   The object of product.
   * @param string $key
   *   Name of attribute.
   *
   * @return string|null
   *   Value of field or null if empty.
   */
  public function getAttributeValue($sku_id, $key) {
    $query = \Drupal::database()->select('acq_sku__attributes', 'acq_sku__attributes');
    $query->addField('acq_sku__attributes', 'attributes_value');
    $query->condition("acq_sku__attributes.entity_id", $sku_id);
    $query->condition("acq_sku__attributes.attributes_key", $key);
    return $query->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function cartName(SKU $sku, array $cart, $asString = FALSE) {
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
      $attribute_value = $this->getAttributeValue($sku->id(), $key);
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

    // If the cart name has already been constructed and is rendered as a link,
    // use the title directly.
    if (!empty($cart['name']['#title'])) {
      $cartName = $cart['name']['#title'];
    }
    else {
      // Create name from label parts.
      $cartName = sprintf(
        '%s (%s)',
        $cart['name'],
        implode(', ', $label_parts)
      );
    }

    if (!$asString) {
      $display_node = $this->getDisplayNode($parent_sku);

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
   * {@inheritdoc}
   */
  public function getProcessedStock(SKU $sku, $reset = FALSE) {
    $stock = &drupal_static('stock_static_cache', []);

    if (!$reset && isset($stock[$sku->getSku()])) {
      return $stock[$sku->getSku()];
    }

    $quantities = [];

    foreach ($sku->get('field_configured_skus') as $child_sku) {
      try {
        $child_sku = $child_sku->getString();
        $child_stock = (int) $this->getStock($child_sku, $reset);
        $quantities[$child_sku] = $child_stock;
      }
      catch (\Exception $e) {
        // Child SKU might be deleted or translation not available.
        // Log messages are already set in previous functions.
      }
    }

    $stock[$sku->getSku()] = empty($quantities) ? 0 : max($quantities);

    return $stock[$sku->getSku()];
  }

  /**
   * Helper function to sort config options based on taxonomy term weight.
   *
   * @param array $options
   *   Option values keyed by option id.
   * @param string $attribute_code
   *   Attribute name.
   *
   * @return array
   *   Array of options sorted based on term weight.
   */
  public static function sortConfigOptions($options, $attribute_code) {
    $sorted_options = [];

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

    return $sorted_options ?: $options;
  }

  /**
   * Wrapper function to get available children for a configurable SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Configurable SKU.
   *
   * @return array
   *   Full loaded child SKUs.
   */
  public static function getChildren(SKU $sku) {
    $children = [];

    foreach ($sku->get('field_configured_skus')->getValue() as $child) {
      if (empty($child['value'])) {
        continue;
      }

      $child_sku = SKU::loadFromSku($child['value']);
      if ($child_sku instanceof SKU) {
        $children[$child_sku->getSKU()] = $child_sku;
      }
    }

    return $children;
  }

}
