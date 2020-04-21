<?php

namespace Drupal\alshaya_acm_checkoutcom\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\UserInterface;

/**
 * Checks access for displaying configuration translation page.
 */
class PaymentCardsPageAccessCheck implements AccessInterface {

  /**
   * Api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * PaymentCardsController constructor.
   *
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   The api helper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ApiHelper $api_helper, ConfigFactoryInterface $config_factory) {
    $this->apiHelper = $api_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * Helper method to check access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account object.
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function access(AccountInterface $account, UserInterface $user) {
    $config = $this->configFactory->get('alshaya_user.settings');
    $enabled_links = $config->get('my_account_enabled_links');
    $enabled_links = unserialize($enabled_links);

    return AccessResult::allowedIf(
      !empty($user->get('acq_customer_id')->getString())
        && $account->id() == $user->id()
        && $this->apiHelper->getCheckoutcomConfig('vault_enabled')
        && !empty($enabled_links['payment_cards'])
    )->addCacheableDependency($config);
  }

}
