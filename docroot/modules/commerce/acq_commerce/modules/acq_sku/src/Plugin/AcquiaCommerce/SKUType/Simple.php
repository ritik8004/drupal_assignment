<?php

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_sku\AcquiaCommerce\SKUPluginBase;
use Drupal\Core\Cache\Cache;
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

    $form['sku_id'] = [
      '#type' => 'hidden',
      '#value' => $sku->id(),
    ];

    $form['quantity'] = [
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
   * {@inheritdoc}
   */
  public function addToCartSubmit(array &$form, FormStateInterface $form_state) {
    $cart = \Drupal::service('acq_cart.cart_storage')->getCart();
    $sku_entity = SKU::load($form_state->getValue('sku_id'));
    $sku = $sku_entity->getSku();
    $quantity = $form_state->getValue('quantity');

    drupal_set_message(
      t('Added @quantity of @name to the cart.',
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
      // Clear stock cache.
      $stock_cid = acq_sku_get_stock_cache_id($sku_entity);
      \Drupal::cache('data')->invalidate($stock_cid);

      // Clear product and forms related to sku.
      Cache::invalidateTags(['acq_sku:' . $sku_entity->id()]);

      // Dispatch event so action can be taken.
      $dispatcher = \Drupal::service('event_dispatcher');
      $event = new AddToCartErrorEvent($e);
      $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
    }
  }

}
