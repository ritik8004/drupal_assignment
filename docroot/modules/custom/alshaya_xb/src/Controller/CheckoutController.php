<?php

namespace Drupal\alshaya_xb\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class Checkout Controller.
 */
class CheckoutController extends ControllerBase {

  /**
   * Returns the checkout page.
   *
   * @return array
   *   Markup for checkout page.
   */
  public function checkoutPage() {
    // Return an empty page with required scripts.
    // Global-e will add the markup for international checkout with Javascript.
    return [
      '#markup' => '',
      '#attached' => [
        'library' => [
          'alshaya_react/react',
          'alshaya_spc/commerce_backend.cart',
          'alshaya_xb/alshaya_xb_checkout_seo',
        ],
        // Set empty payment methods in settings.
        // This is used for GTM and populated with data from global-e.
        'drupalSettings' => [
          'payment_methods' => [],
        ],
      ],
    ];
  }

}
