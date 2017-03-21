<?php

namespace Drupal\acq_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\acq_cart\CartStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class getMiniCartRender
 * @package Drupal\acq_cart\Controller
 */
class getMiniCartRender extends ControllerBase {
  /**
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   */
  public function __construct(CartStorageInterface $cart_storage) {
    $this->cartStorage = $cart_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content() {
    $cart = $this->cartStorage->getCart();
    $totals = $cart->totals();

    // Fetch the currency format from the config factor.
    $currency_format = \Drupal::configFactory()
      ->getEditable('acq_commerce.currency')
      ->get('currency_code');

    // The grand total including discounts and taxes.
    $grand_total = $totals['grand'] < 0 || $totals['grand'] == NULL ? 0 : $totals['grand'];

    // The number of items in cart.
    $items = $this->cartStorage->getCart()->items();
    $quantity = 0;
    foreach ($items as $item) {
      $quantity += $item['qty'];
    }

    // Use the template to render the HTML.
    $output = [
      '#theme' => 'acq_cart_mini_cart',
      '#quantity' => $quantity,
      '#total' => $grand_total,
      '#currency_format' => $currency_format,
    ];

    return new JsonResponse(drupal_render($output)->jsonSerialize());
  }
}
