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
    return $this->t('secure checkout');
  }

}
