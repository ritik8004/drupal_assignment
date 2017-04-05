<?php

/**
 * @file
 * Contains Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Grouped;
 */

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_sku\AcquiaCommerce\SKUPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_cart\Entity\Cart;
use Drupal\acq_cart\Entity\LineItem;
use Drupal\acq_commerce\LineItemInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\AddToCartErrorEvent;

/**
 * Defines the grouped SKU type
 *
 * @SKUType(
 *   id = "grouped",
 *   label = @Translation("Grouped SKU"),
 *   description = @Translation("Grouped SKU for picking out a grouped product."),
 * )
 */
class Grouped extends SKUPluginBase {
  /**
   * {@inheritdoc}
   */
  public function addToCartForm($form, FormStateInterface $form_state, SKU $sku = NULL) {
    if (empty($sku)) {
      return $form;
    }

    $form['grouped_items'] = [
      '#type' => 'table',
      '#header' => [
        t('Product'),
        t('Quantity'),
        t('Price'),
      ],
      '#empty' => t('This grouped product has no items.'),
    ];

    foreach ($sku->field_grouped_skus as $grouped_sku) {
      $grouped_sku = SKU::loadFromSKU($grouped_sku->getString());
      $id = $grouped_sku->getSKU();

      $form['grouped_items'][$id]['name'] = [
        '#plain_text' => $grouped_sku->label(),
      ];

      $form['grouped_items'][$id]['quantity'] = [
        '#type' => 'number',
        '#default_value' => 0,
      ];

      $form['grouped_items'][$id]['price'] = [
        '#plain_text' => $grouped_sku->price->first()->value,
      ];
    }

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
    $skus = $form_state->getValue('grouped_items');

    $added = 0;

    foreach ($skus as $sku => $quantity) {
      $quantity = (int) $quantity['quantity'];
      if ($quantity > 0) {
        $cart->addItemToCart($sku, $quantity);

        drupal_set_message(
          t('Added @quantity of @name to the cart.' ,
            [
              '@quantity' => $quantity,
              '@name' => SKU::loadFromSKU($sku)->label()
            ]
        ));

        $added++;
      }
    }

    if ($added == 0) {
      drupal_set_message(t('Please select a quantity greater than 0.'), 'error');
    }

    try {
      \Drupal::service('acq_cart.cart_storage')->updateCart();
    }
    catch (\Exception $e) {
      // Dispatch event so action can be taken.
      $dispatcher = \Drupal::service('event_dispatcher');
      $event = new AddToCartErrorEvent($e);
      $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processImport($sku, $product) {
    $sku->field_grouped_skus->setValue([]);

    foreach ($product['linked'] as $linked_sku) {
      $sku->field_grouped_skus->set(
        $linked_sku['position'],
        $linked_sku['linked_sku']
      );
    }
  }
}
