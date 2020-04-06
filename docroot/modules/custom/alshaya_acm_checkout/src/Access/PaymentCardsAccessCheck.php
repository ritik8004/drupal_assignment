<?php

namespace Drupal\alshaya_acm_checkout\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\UserInterface;
use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxy;

/**
 * Checks access for '/payment-cards' page.
 */
class PaymentCardsAccessCheck implements AccessInterface {

  /**
   * API Helper Service.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * Account object.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $account;

  /**
   * CurrentRouteMatch service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The class constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The CurrentRoutematch service object.
   * @param \Drupal\Core\Session\AccountProxy $account
   *   The AccountProxy object.
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   The ApiHelper service object.
   */
  public function __construct(
    CurrentRouteMatch $current_route_match,
    AccountProxy $account,
    ApiHelper $api_helper
  ) {
    $this->apiHelper = $api_helper;
    $this->account = $account;
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * Checking access for Payments Card page.
   *
   *   Only those users can access the page who satisfy the following criteria:
   *   1. Is visiting theiry own Payments page
   *   2. Is a customer
   *   3. When the subscription keys for checkout.com exists.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access() {
    // Check permissions and combine that with any custom access checking
    // needed. Pass forward parameters from the route and/or request as needed.
    $requirement = $this->currentRouteMatch->getRouteObject()->getRequirement('_payment_cards_access');
    $requirement = TRUE;
    if ($requirement) {
      $route_user_obj = $this->currentRouteMatch->getParameter('user');
      $user = $route_user_obj instanceof UserInterface ? $route_user_obj : NULL;
      $user_id = $user->id();

      if ($user_id) {
        return AccessResult::allowedIf(
          ($this->account->id() === $user_id)
          && alshaya_acm_customer_is_customer($user)
          && $this->apiHelper->getCheckoutcomConfig('vault_enabled')
        );
      }
    }

    return AccessResult::neutral();
  }

}
