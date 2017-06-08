<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Multi step checkout panes custom titles.
 */
class CheckoutPaneTitles extends ControllerBase {

  /**
   * Page title for checkout steps page.
   */
  public function checkoutPageTitle(RouteMatchInterface $route_match) {
    // Current checkout step.
    $current_step = $route_match->getParameter('step');

    if ($current_step == 'login') {
      return t('Welcome');
    }

    return t('secure checkout');
  }

}
