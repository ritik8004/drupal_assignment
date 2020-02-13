<?php

namespace Drupal\alshaya_acm_checkout\EventSubscriber;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CartDyPageTypeEventSubscriber.
 *
 * @package Drupal\alshaya_acm_checkout\EventSubscriber
 */
class CartDyPageTypeEventSubscriber implements EventSubscriberInterface {

  /**
   * Checkout Helper.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutHelper
   */
  protected $helper;

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Cart Storage.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * CartDyPageTypeEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    CartStorageInterface $cart_storage
  ) {
    $this->routeMatch = $route_match;
    $this->cartStorage = $cart_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['dy.set.context'][] = ['setContextCart', 150];
    return $events;
  }

  /**
   * Set Dynamic yield Context to CART.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   Dispatched Event.
   */
  public function setContextCart(Event $event) {
    if ($this->routeMatch->getRouteName() == 'acq_cart.cart') {
      $event->setDyContext('CART');
      $cart = $this->cartStorage->getCart(FALSE);
      if (!empty($cart)) {
        $items = $cart->items();
        $data = [];
        foreach ($items as $item) {
          $data[] = $item['sku'];
        }
        $event->setDyContextData($data);
      }
    }
  }

}
