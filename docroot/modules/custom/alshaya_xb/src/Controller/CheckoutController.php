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
    // Return an empty page. Global-e will add the markup for international
    // checkout with Javascript.
    return [
      '#markup' => '',
    ];
  }

}
