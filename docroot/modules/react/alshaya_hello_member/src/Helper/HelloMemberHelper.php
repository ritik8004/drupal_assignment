<?php

namespace Drupal\alshaya_hello_member\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;

/**
 * Helper class for Hello Member.
 *
 * @package Drupal\alshaya_hello_member\Helper
 */
class HelloMemberHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Temp storage to keep session values.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStorage;

  /**
   * Hello Member constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_storage
   *   Used as temporary storage.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    PrivateTempStoreFactory $temp_storage
  ) {
    $this->configFactory = $config_factory;
    $this->tempStorage = $temp_storage->get('alshaya_hello_member');
  }

  /**
   * Helper to check if Hello Member is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isHelloMemberEnabled() {
    return $this->getConfig()->get('status');
  }

  /**
   * Helper to check if Aura integration with hello member is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isAuraIntegrationEnabled() {
    return $this->getConfig()->get('aura_integration_status');
  }

  /**
   * Helper to get Cache Tags for Hello member Config.
   *
   * @return string[]
   *   A set of cache tags.
   */
  public function getCacheTags() {
    return $this->getConfig()->getCacheTags();
  }

  /**
   * Wrapper function to get Hello member Config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Online Returns Config.
   */
  public function getConfig() {
    static $config;

    if (is_null($config)) {
      $config = $this->configFactory->get('alshaya_hello_member.settings');
    }

    return $config;
  }

  /**
   * Helper function to store the OTP status in temp store.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity object.
   * @param \Drupal\Core\Form\FormStateInterface|null $form_state
   *   The form state object.
   */
  public function storeMobileVerificationStatus(UserInterface $user, $form_state = NULL) {
    // If we have the info in form state then use that else check for the user
    // object.
    if ($form_state instanceof FormStateInterface && $form_state->getValue('otp_verified')) {
      // Set the key/value pair.
      $this->tempStorage->set('otpVerifiedPhone', $form_state->getValue('field_mobile_number')[0]['mobile']);
    }
    elseif ($user->hasField('field_mobile_number') && $user->get('field_mobile_number')) {
      $this->tempStorage->set('otpVerifiedPhone', $user->get('field_mobile_number')->getValue());
    }
  }

}
