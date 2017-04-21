<?php

namespace Drupal\acq_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\acq_cart\CartStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class getMiniCart
 * @package Drupal\acq_cart\Controller
 */
class getMiniCart extends ControllerBase {
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

    if (empty($cart)) {
      // Something is wrong, but we have already logged at API level.
      // Just respond back with empty text to avoid issues.
      return new JsonResponse([]);
    }

    $totals = $cart->totals();

    // Fetch the config.
    $config = \Drupal::configFactory()
      ->get('acq_commerce.currency');

    // Fetch the currency format from the config factor.
    $currency_format = $config->get('currency_code');

    // Fetch the currency code position.
    $currency_code_position = $config->get('currency_code_position');

    // The grand total including discounts and taxes.
    $grand_total = $totals['grand'] < 0 || $totals['grand'] == NULL ? 0 : $totals['grand'];

    // Use the template to render the HTML.
    $output = [
      '#theme' => 'acq_cart_mini_cart',
      '#quantity' => $cart->getCartItemsCount(),
      '#total' => $grand_total,
      '#currency_format' => $currency_format,
      '#currency_code_position' => $currency_code_position,
    ];

    return new JsonResponse(drupal_render($output)->jsonSerialize());
  }
}
