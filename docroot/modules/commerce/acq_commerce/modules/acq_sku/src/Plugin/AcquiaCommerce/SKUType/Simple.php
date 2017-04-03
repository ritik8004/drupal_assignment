<?php

/**
 * @file
 * Contains Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Simple;
 */

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_sku\AcquiaCommerce\SKUPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_cart\Entity\Cart;
use Drupal\acq_cart\Entity\LineItem;
use Drupal\acq_commerce\LineItemInterface;
use Drupal\acq_sku\Entity\SKU;

/**
 * Defines the simple SKU type
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
  public function addToCartForm($form, FormStateInterface $form_state, SKU $sku = NULL) {
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
  public function addToCartSubmit(&$form, FormStateInterface $form_state) {
    $cart = \Drupal::service('acq_cart.cart_storage')->getCart();
    $sku_entity = SKU::load($form_state->getValue('sku_id'));
    $sku = $sku_entity->getSKU();
    $quantity = $form_state->getValue('quantity');

    drupal_set_message(
      t('Added @quantity of @name to the cart.' ,
      [
        '@quantity' => $quantity,
        '@name' => $sku_entity->name->value
      ]
    ));

    $cart->addItemToCart($sku, $quantity);

    $response = \Drupal::service('acq_cart.cart_storage')->updateCart();

    // Show errors for updating the cart.
    if ($response->code == 0) {
      // @todo: Use a better way to show errors.
      // @todo: Check if we can use the same cart notification to show errors.
      drupal_set_message($response->message);
    }
  }
}
