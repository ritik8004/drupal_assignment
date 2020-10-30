<?php

namespace Drupal\alshaya_acm_checkoutcom\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Service payment cards page from Alshaya code.
    $payment_cards_route = $collection->get('acq_checkoutcom.payment_cards');
    if ($payment_cards_route) {
      $payment_cards_route->setDefault(
        '_controller',
        '\Drupal\alshaya_acm_checkoutcom\Controller\AlshayaPaymentCardsController::listCards'
      );

      $payment_cards_route->setRequirement(
        '_custom_access',
        '\Drupal\alshaya_acm_checkoutcom\Controller\AlshayaPaymentCardsController::checkAccess'
      );
    }

    $payment_card_remove_route = $collection->get('acq_checkoutcom.payment_card.remove_card');
    if ($payment_card_remove_route) {
      $payment_card_remove_route->setDefault(
        '_controller',
        '\Drupal\alshaya_acm_checkoutcom\Controller\AlshayaPaymentCardsController::removeCard'
      );

      $payment_card_remove_route->setRequirement(
        '_custom_access',
        '\Drupal\alshaya_acm_checkoutcom\Controller\AlshayaPaymentCardsController::checkAccess'
      );
    }
  }

}
