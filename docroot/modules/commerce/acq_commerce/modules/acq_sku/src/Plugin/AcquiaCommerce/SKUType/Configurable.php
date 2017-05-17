<?php

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_sku\AcquiaCommerce\SKUPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Link;
use Drupal\acq_sku\AddToCartErrorEvent;

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

    foreach ($configurables as $configurable) {
      $attribute_code = $configurable['code'];

      $options = [];

      foreach ($configurable['values'] as $value) {
        $options[$value['value_id']] = $value['label'];
      }

      $form['ajax']['configurables'][$attribute_code] = [
        '#type' => 'select',
        '#title' => $configurable['label'],
        '#options' => $options,
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

    $form['sku_id'] = [
      '#type' => 'hidden',
      '#value' => $sku->id(),
    ];

    $form['quantity'] = [
      '#title' => t('Quantity'),
      '#type' => 'number',
      '#default_value' => 1,
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

      $cart->addRawItemToCart([
        'name' => $label,
        'sku' => $tree['parent']->getSKU(),
        'qty' => $quantity,
        'options' => [
          'configurable_item_options' => $options,
        ],
      ]);

      drupal_set_message(
        t('Added @quantity of @name to the cart.',
          [
            '@quantity' => $quantity,
            '@name' => $label,
          ]
      ));

      $cart->addItemToCart($tree_pointer->getSku(), $quantity);

      // Add child SKU to form state to allow other modules to use it.
      $form_state->setTemporaryValue('child_sku', $tree_pointer->getSKU());

      try {
        \Drupal::service('acq_cart.cart_storage')->updateCart();
      }
      catch (\Exception $e) {
        // Remove item from cart.
        $cart->addItemToCart($tree_pointer->getSku(), -$quantity);
        $cart->addItemToCart($tree['parent']->getSKU(), -$quantity);
        _acq_cart_remove_zero_quantity_item($cart);

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

    foreach ($configurables as $configurable) {
      $tree['configurables'][$configurable['code']] = $configurable;
    }

    $tree['options'] = Configurable::recursiveConfigurableTree(
      $tree,
      $tree['configurables']
    );

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
    $sku = $tree['parent']->getSKU();
    $query = \Drupal::database()->select('acq_sku', 'acq_sku');

    $query->addField('acq_sku', 'sku');
    $query->condition('sku', "%$sku%", 'LIKE');

    foreach ($config as $key => $value) {
      $query->join('acq_sku__attributes', $key, "acq_sku.id = $key.entity_id");
      $query->condition("$key.attributes_key", $key);
      $query->condition("$key.attributes_value", $value);
    }

    $sku = $query->execute()->fetchField();
    return $tree['products'][$sku];
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
    $query = \Drupal::database()->select('acq_sku', 'acq_sku');
    $query->addField('acq_sku', 'sku');
    $query->join(
      'acq_sku__field_configured_skus',
      'child_sku', 'acq_sku.id = child_sku.entity_id'
    );
    $query->condition("child_sku.field_configured_skus_value", $sku->getSku());
    $parent_sku = $query->execute()->fetchField();

    if (empty($parent_sku)) {
      return NULL;
    }

    return SKU::loadFromSku($parent_sku);
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
