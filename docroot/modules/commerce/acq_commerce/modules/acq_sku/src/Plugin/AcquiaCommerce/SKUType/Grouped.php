<?php

namespace Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType;

use Drupal\acq_sku\AcquiaCommerce\SKUPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\AddToCartErrorEvent;

/**
 * Defines the grouped SKU type.
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
  public function addToCartForm(array $form, FormStateInterface $form_state, SKU $sku = NULL) {
    if (empty($sku)) {
      return $form;
    }

    $form['grouped_items'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Product'),
        $this->t('Quantity'),
        $this->t('Price'),
      ],
      '#empty' => $this->t('This grouped product has no items.'),
    ];

    foreach ($sku->field_grouped_skus as $grouped_sku) {
      $grouped_sku = SKU::loadFromSku($grouped_sku->getString());
      $id = $grouped_sku->getSku();

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
      '#value' => $this->t('Add to cart'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addToCartSubmit(array &$form, FormStateInterface $form_state) {
    $cart = \Drupal::service('acq_cart.cart_storage')->getCart();
    $skus = $form_state->getValue('grouped_items');

    $added = 0;

    foreach ($skus as $sku => $quantity) {
      $quantity = (int) $quantity['quantity'];
      if ($quantity > 0) {
        $cart->addItemToCart($sku, $quantity);

        $this->messenger()->addMessage(
          $this->t('Added @quantity of @name to the cart.',
            [
              '@quantity' => $quantity,
              '@name' => SKU::loadFromSku($sku)->label(),
            ]
        ));

        $added++;
      }
    }

    if ($added == 0) {
      $this->messenger()->addMessage($this->t('Please select a quantity greater than 0.'), 'error');
    }

    try {
      \Drupal::service('acq_cart.cart_storage')->updateCart();
    }
    catch (\Exception $e) {
      // @todo Handle clearing stock cache for grouped products.
      // Dispatch event so action can be taken.
      $dispatcher = \Drupal::service('event_dispatcher');
      $event = new AddToCartErrorEvent($e);
      $dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, $event);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processImport($sku, array $product) {
    $sku->field_grouped_skus->setValue([]);

    foreach ($product['linked'] as $linked_sku) {
      // Linked may contain associated, upsell, crosssell and related.
      // We want only the associated ones for grouped.
      if ($linked_sku['type'] == 'associated') {
        $sku->field_grouped_skus->set(
          $linked_sku['position'],
          $linked_sku['linked_sku']
        );
      }
    }
  }

}
