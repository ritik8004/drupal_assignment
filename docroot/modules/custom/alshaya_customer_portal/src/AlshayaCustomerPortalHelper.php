<?php

namespace Drupal\alshaya_customer_portal;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Site\Settings;
use Drupal\user\UserInterface;

/**
 * Alshaya Customer Portal Helper class.
 */
class AlshayaCustomerPortalHelper {

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Logger service object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs the AlshayaCustomerPortalHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger service object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    RendererInterface $renderer,
    LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
    $this->logger = $logger_factory->get('alshaya_customer_portal');
  }

  /**
   * Returns encrypted part of SSO URL for Customer portal link.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   *
   * @return string
   *   The encrypted string that is ready to be used for SSO.
   */
  public function getEncryptedDataForCustomerPortal(UserInterface $user) {
    $secret_key = Settings::get('alshaya_customer_portal')['encryption_secret_key'];
    $encrypted_string = '';
    if (empty($secret_key)) {
      $this->logger->notice('No secret key provided for Customer Portal!');
      return $encrypted_string;
    }
    $map = $this->mapUserWithCustomerPortalFields($user, $secret_key);
    // Format the data in the form "a=b&c=d".
    $final_data = '';
    foreach ($map as $key => $value) {
      $final_data .= $key . '=' . $value . '&';
    }
    // Remove the trailing &.
    $final_data = rtrim($final_data, '&');
    // Encrypt the data.
    $encrypted_string = $this->encrypt($final_data, $secret_key, 'AES-256-CBC');
    // Remove some special characters to make it URL friendly as per the
    // requirement of customer portal. Documentation for this can be found in
    // "Configuring-pass-through-authentication.pdf" attached to ticket
    // https://alshayagroup.atlassian.net/browse/CORE-16466.
    $encrypted_string = $this->cleanSpecialCharactersForUrl($encrypted_string);

    return $encrypted_string;
  }

  /**
   * Maps the required keys for Customer Portal with the values of user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param string $key
   *   The encryption key.
   *
   * @return array
   *   The mapped array.
   */
  protected function mapUserWithCustomerPortalFields(UserInterface $user, string $key) {
    return [
      'p_li_passwd' => $key,
      'p_userid' => $user->getEmail(),
      'p_email.addr' => $user->getEmail(),
      'p_name.first' => $user->get('field_first_name')->getString(),
      'p_name.last' => $user->get('field_last_name')->getString(),
    ];
  }

  /**
   * Removes special characters from encoded string and prepares it for URL.
   *
   *  This is as per the requirement of Customer Portal.
   *
   * @param string $string
   *   The string to clean.
   *
   * @return string
   *   The string without the special characters '+', '/' and '='.
   */
  protected function cleanSpecialCharactersForUrl(string $string) {
    $string = str_replace('+', '_', $string);
    $string = str_replace('/', '~', $string);
    $string = str_replace('=', '*', $string);

    return $string;
  }

  /**
   * Encrypts data using AES-256-CBC algorithm.
   *
   * @return string
   *   The encrypted string.
   *
   * @throws Exception
   */
  protected function encrypt(string $data, string $key, string $method) {
    // Get the initialization vector.
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    return trim(openssl_encrypt($data, $method, $key, 0, $iv));
  }

}
