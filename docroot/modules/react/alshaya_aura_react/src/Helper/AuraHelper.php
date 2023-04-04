<?php

namespace Drupal\alshaya_aura_react\Helper;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\alshaya_aura_react\Constants\AuraDictionaryApiConstants;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * @var \Drupal\alshaya_aura_react\Helper\AuraApiHelper
   */
  protected $apiHelper;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    MobileNumberUtilInterface $mobile_util,
    ConfigFactoryInterface $config_factory,
    AuraApiHelper $api_helper,
    LanguageManagerInterface $language_manager
  ) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->mobileUtil = $mobile_util;
    $this->configFactory = $config_factory;
    $this->apiHelper = $api_helper;
    $this->languageManager = $language_manager;
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
    $dictionary_api_mobile_country_code_list = $this->apiHelper->getAuraApiConfig([AuraDictionaryApiConstants::EXT_PHONE_PREFIX],
    $this->languageManager->getCurrentLanguage()->getId());

    $config = [
      'siteName' => $this->configFactory->get('system.site')->get('name'),
      'appStoreLink' => $alshaya_aura_config->get('aura_app_store_link'),
      'googlePlayLink' => $alshaya_aura_config->get('aura_google_play_link'),
      'country_mobile_code' => $country_mobile_code,
      'mobile_maxlength' => $this->configFactory->get('alshaya_master.mobile_number_settings')->get('maxlength'),
      'headerLearnMoreLink' => $alshaya_aura_config->get('aura_rewards_header_learn_more_link'),
      'phonePrefixList' => $dictionary_api_mobile_country_code_list[AuraDictionaryApiConstants::EXT_PHONE_PREFIX] ?? ['+' . $country_mobile_code],
      'rewardActivityTimeLimit' => $alshaya_aura_config->get('aura_reward_activity_time_limit_in_months'),
      'signUpTermsAndConditionsLink' => $alshaya_aura_config->get('aura_signup_terms_and_conditions_link'),
      'auraUsernameCharacterLimit' => $alshaya_aura_config->get('aura_username_character_limit'),
      'isoCurrencyCode' => $this->configFactory->get('acq_commerce.currency')->get('iso_currency_code'),
      'auraUnsupportedPaymentMethods' => $alshaya_aura_config->get('aura_unsupported_payment_methods'),
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
