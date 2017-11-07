<?php

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_commerce\Conductor\APIWrapper;
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

    $configurable_form_settings = \Drupal::service('config.factory')->get('acq_sku.configurable_form_settings');

    $form['sku_id'] = [
      '#type' => 'hidden',
      '#value' => $sku->id(),
    ];

    $form['quantity'] = [
      '#title' => t('Quantity'),
      '#type' => 'number',
      '#default_value' => 1,
      '#required' => TRUE,
      '#access' => $configurable_form_settings->get('show_quantity'),
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
      \Drupal::cache('stock')->invalidate($stock_cid);

      // Clear product and forms related to sku.
      Cache::invalidateTags(['acq_sku:' . $sku_entity->id()]);

      // Dispatch event so action can be taken.
      $dispatcher = \Drupal::service('event_dispatcher');
      $event = new AddToCartErrorEvent($e);
      $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
    }
  }

}
