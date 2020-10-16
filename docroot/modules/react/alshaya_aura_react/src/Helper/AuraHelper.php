<?php

namespace Drupal\alshaya_aura_react\Helper;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class AuraHelper.
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

    $config = [
      'appStoreLink' => $alshaya_aura_config->get('aura_app_store_link'),
      'googlePlayLink' => $alshaya_aura_config->get('aura_google_play_link'),
      'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
      'mobile_maxlength' => $this->configFactory->get('alshaya_master.mobile_number_settings')->get('maxlength'),
    ];

    return $config;
  }

  /**
   * Get Static Strings.
   *
   * @return array
   *   Array of static strings.
   */
  public function getStaticStrings() {
    return [
      [
        'key' => 'form_error_valid_mobile_number',
        'value' => $this->t('Please enter valid mobile number.'),
      ],
      [
        'key' => 'form_error_mobile_number',
        'value' => $this->t('Please enter mobile number.'),
      ],
      [
        'key' => 'form_error_otp',
        'value' => $this->t('Please enter OTP.'),
      ],
      [
        'key' => 'otp_send_message',
        'value' => $this->t('We have sent the One Time Pin to your mobile number.'),
      ],
      [
        'key' => 'didnt_receive_otp_message',
        'value' => $this->t('Didn’t receive the One Time Pin?'),
      ],
      [
        'key' => 'send_otp_helptext',
        'value' => $this->t('We will send a One Time Pin to both your email address and mobile number.'),
      ],
      [
        'key' => 'verify',
        'value' => $this->t('Verify'),
      ],
      [
        'key' => 'otp_button_label',
        'value' => $this->t('Send One Time Pin'),
      ],
      [
        'key' => 'otp_modal_title',
        'value' => $this->t('Say hello to Aura'),
      ],
      [
        'key' => 'resend_code',
        'value' => $this->t('Resend Code'),
      ],
      [
        'key' => 'mobile_label',
        'value' => $this->t('Mobile Number'),
      ],
      [
        'key' => 'otp_label',
        'value' => $this->t('One Time Pin'),
      ],
    ];
  }

}
