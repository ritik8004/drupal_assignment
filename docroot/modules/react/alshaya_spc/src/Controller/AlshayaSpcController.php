<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AlshayaSpcController.
 */
class AlshayaSpcController extends ControllerBase {

  /**
   * Overridden controller for cart page.
   *
   * @return array
   *   Markup for cart page.
   */
  public function cart() {
    return [
      '#type' => 'markup',
      '#markup' => '<div id="spc-cart"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/cart',
          'alshaya_white_label/spc-cart',
        ],
      ],
    ];
  }

}
