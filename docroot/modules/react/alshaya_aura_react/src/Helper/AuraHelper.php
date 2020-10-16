<?php

namespace Drupal\alshaya_aura_react\Helper;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class AuraHelper.
 *
 * @package Drupal\alshaya_aura_react\Helper
 */
class AuraHelper {
  /**
   * The current user making the request.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AuraHelper constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    MobileNumberUtilInterface $mobile_util,
    ConfigFactoryInterface $config_factory
  ) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->mobileUtil = $mobile_util;
    $this->configFactory = $config_factory;
  }

  /**
   * Get user's AURA Status.
   *
   * @return string
   *   User's AURA Status.
   */
  public function getUserAuraStatus() {
    $uid = $this->currentUser->id();
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    $status = $user->get('field_aura_loyalty_status')->getString() ?? '';

    return $status;
  }

  /**
   * Get user's AURA Tier.
   *
   * @return string
   *   User's AURA Tier.
   */
  public function getUserAuraTier() {
    $uid = $this->currentUser->id();
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    $tier = $user->get('field_aura_tier')->getString() ?? '';

    return $tier;
  }

  /**
   * Get aura config.
   *
   * @return array
   *   AURA config.
   */
  public function getAuraConfig() {
    $country_code = _alshaya_custom_get_site_level_country_code();
    $alshaya_aura_config = $this->configFactory->get('alshaya_aura_react.settings');

    $config = [
      'appStoreLink' => $alshaya_aura_config->get('aura_app_store_link'),
      'googlePlayLink' => $alshaya_aura_config->get('aura_google_play_link'),
      'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
      'mobile_maxlength' => $this->configFactory->get('alshaya_master.mobile_number_settings')->get('maxlength'),
    ];

    return $config;
  }

}
