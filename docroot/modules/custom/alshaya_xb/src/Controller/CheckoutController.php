<?php

namespace Drupal\alshaya_xb\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class Checkout Controller.
 */
class CheckoutController extends ControllerBase {

  /**
   * Returns an empty page.
   *
   * @return array
   *   Markup for checkout page.
   */
  public function emptyPage() {
    return [
      '#markup' => '',
    ];
  }

}
