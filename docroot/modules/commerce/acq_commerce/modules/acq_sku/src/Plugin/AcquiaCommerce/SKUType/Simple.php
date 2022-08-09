<?php

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_sku\AcquiaCommerce\SKUPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\AddToCartErrorEvent;

/**
 * Defines the simple SKU type.
 *
 * @SKUType(
 *   id = "simple",
 *   label = @Translation("Simple SKU"),
 *   description = @Translation("Simple SKU for buying a single SKU"),
 * )
 */
class Simple extends SKUPluginBase {

  /**
   * {@inheritdoc}
   */
  public function addToCartForm(array $form, FormStateInterface $form_state, SKU $sku = NULL) {
    if (empty($sku)) {
      return $form;
    }

    /** @var \Drupal\acq_sku\CartFormHelper $helper */
    $helper = \Drupal::service('acq_sku.cart_form_helper');

    $form['sku_id'] = [
      '#type' => 'hidden',
      '#value' => $sku->id(),
    ];

    $form['quantity'] = [
      '#title' => $this->t('Quantity'),
      '#type' => 'number',
      '#default_value' => 1,
      '#required' => TRUE,
      '#access' => $helper->showQuantity(),
      '#size' => 2,
    ];

    $form['add_to_cart'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to cart'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addToCartSubmit(array &$form, FormStateInterface $form_state) {
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

    $sku_entity = SKU::load($form_state->getValue('sku_id'));
    $sku = $sku_entity->getSku();
    $quantity = $form_state->getValue('quantity');

    $this->messenger()->addStatus(
      $this->t('Added @quantity of @name to the cart.',
      [
        '@quantity' => $quantity,
        '@name' => $sku_entity->name->value,
      ]
    ));

    $cart->addItemToCart($sku, $quantity);

    try {
      \Drupal::service('acq_cart.cart_storage')->updateCart();
    }
    catch (\Exception $e) {
      $this->refreshStock($sku_entity);

      // Dispatch event so action can be taken.
      $dispatcher = \Drupal::service('event_dispatcher');
      $event = new AddToCartErrorEvent($e);
      $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getParentSku(SKU $sku, bool $with_node = TRUE) {
    $sku_string = $sku->getSku();
    $parents = $this->getAllParentIds($sku_string);

    foreach ($parents as $parent_sku) {
      $parent = SKU::loadFromSku($parent_sku, $sku->language()->getId());
      if ($parent instanceof SKU) {
        if ($with_node) {
          if ($this->getDisplayNodeId($parent)) {
            return $parent;
          }
        }
        else {
          return $parent;
        }
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllParentIds(string $sku): array {
    $static = &drupal_static(__FUNCTION__, []);

    if (isset($static[$sku])) {
      return $static[$sku];
    }

    $query = \Drupal::database()->select('acq_sku_field_data', 'acq_sku');
    $query->addField('acq_sku', 'id');
    $query->addField('acq_sku', 'sku');
    $query->join('acq_sku__field_configured_skus', 'child_sku', 'acq_sku.id = child_sku.entity_id');
    $query->condition('child_sku.field_configured_skus_value', $sku);
    $parents = $query->execute()->fetchAllKeyed();

    if ((is_countable($parents) ? count($parents) : 0) > 1) {
      \Drupal::logger('acq_sku')->warning(
        'Multiple parents found for SKU: @sku, parents: @parents',
        [
          '@parents' => implode(',', $parents),
          '@sku' => $sku,
        ]
      );
    }

    $static[$sku] = $parents ?? [];

    return $static[$sku];
  }

}
