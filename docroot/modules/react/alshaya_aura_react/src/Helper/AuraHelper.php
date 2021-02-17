<?php

namespace Drupal\alshaya_aura_react\Helper;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\alshaya_aura_react\Constants\AuraDictionaryApiConstants;

/**
 * Helper class for Aura.
 *
 * @package Drupal\alshaya_aura_react\Helper
 */
class AuraHelper {

  use StringTranslationTrait;

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
   * The api helper object.
   *
   * @var Drupal\alshaya_aura_react\Helper\AuraApiHelper
   */
  protected $apiHelper;

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
   * @param Drupal\alshaya_aura_react\Helper\AuraApiHelper $api_helper
   *   Api helper object.
   */
  public function __construct(
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    MobileNumberUtilInterface $mobile_util,
    ConfigFactoryInterface $config_factory,
    AuraApiHelper $api_helper
  ) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->mobileUtil = $mobile_util;
    $this->configFactory = $config_factory;
    $this->apiHelper = $api_helper;
  }

  /**
   * Get user's AURA Status.
   *
   * @return string
   *   User's AURA Status.
   */
  public function getUserAuraStatus() {
    $uid = $this->currentUser->id();
    $aura_status_field = $this->entityTypeManager->getStorage('user')->load($uid)->get('field_aura_loyalty_status')->getString() ?? '';
    $status = $aura_status_field !== '' ? $aura_status_field : 0;

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
    $country_mobile_code = $this->mobileUtil->getCountryCode($country_code);
    $dictionary_api_mobile_country_code_list = $this->apiHelper->getAuraApiConfig([AuraDictionaryApiConstants::EXT_PHONE_PREFIX]);

    $config = [
      'siteName' => $this->configFactory->get('system.site')->get('name'),
      'appStoreLink' => $alshaya_aura_config->get('aura_app_store_link'),
      'googlePlayLink' => $alshaya_aura_config->get('aura_google_play_link'),
      'country_mobile_code' => $country_mobile_code,
      'mobile_maxlength' => $this->configFactory->get('alshaya_master.mobile_number_settings')->get('maxlength'),
      'headerLearnMoreLink' => $alshaya_aura_config->get('aura_rewards_header_learn_more_link'),
      'phonePrefixList' => $dictionary_api_mobile_country_code_list[AuraDictionaryApiConstants::EXT_PHONE_PREFIX] ?? ['+' . $country_mobile_code],
      'rewardActivityTimeLimit' => $alshaya_aura_config->get('aura_reward_activity_time_limit_in_months'),
    ];

    return $config;
  }

  /**
   * Helper to check if aura is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isAuraEnabled() {
    return $this->configFactory->get('alshaya_aura_react.settings')->get('aura_enabled');
  }

}
