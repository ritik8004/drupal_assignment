<?php

namespace App\EventSubscriber\Aura;

use App\Event\CartDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Contains Cart Data Event Subscriber methods.
 *
 * @package App\EventSubscriber\Aura
 */
class CartDataSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CartDataEvent::EVENT_NAME => 'processCartData',
    ];
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param App\Event\CartDataEvent $event
   *   Event object.
   */
  public function processCartData(CartDataEvent $event) {
    $cart = $event->getCart();
    $processedCartData = $event->getProcessedCartData();
    $updated = FALSE;

    // Add aura payment details if present to cart.
    if (!empty($cart['totals']['total_segments'])) {
      $aura_payment_key = array_search('aura_payment', array_column($cart['totals']['total_segments'], 'code'));

      if ($aura_payment_key) {
        $processedCartData['totals']['paidWithAura'] = $aura_payment_key
          ? $cart['totals']['total_segments'][$aura_payment_key]['value']
          : 0;
        $updated = TRUE;
      }

      $balance_payable_key = array_search('balance_payable', array_column($cart['totals']['total_segments'], 'code'));
      if ($balance_payable_key) {
        $processedCartData['totals']['balancePayable'] = $aura_payment_key
          ? $cart['totals']['total_segments'][$balance_payable_key]['value']
          : 0;
        $updated = TRUE;
      }
    }

    // Add aura card if present in cart.
    if (!empty($cart['cart']['extension_attributes']['loyalty_card'])) {
      $processedCartData['loyaltyCard'] = $cart['cart']['extension_attributes']['loyalty_card'];
      $updated = TRUE;
    }

    if ($updated === TRUE) {
      $event->setProcessedCartData($processedCartData);
    }
  }

}
