<?php

namespace Drupal\alshaya_customer_portal;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\user\UserInterface;

/**
 * AlshayaCustomerPortalHelper class.
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
   * Constructs the AlshayaCustomerPortalHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RendererInterface $renderer) {
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
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
    $secret_key = $this->configFactory->get('alshaya_customer_portal.settings')->get('encryption_secret_key');
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
    // Remove some special characters to make it URL friendly.
    $encrypted_string = $this->cleanSpecialCharactersForUrl($encrypted_string);

    return $encrypted_string;
  }

  /**
   * Get Iframe markup.
   *
   * @return string
   *   The iframe markup.
   */
  public function getIframeMarkup(string $sso_url, UserInterface $user = NULL) {
    $config = $this->configFactory->get('alshaya_customer_portal.settings');

    $build = [
      '#type' => 'inline_template',
      '#template' => '<iframe
       frameborder="' . $config->get('iframe.attributes.frameborder') . '"
       height="' . $config->get('iframe.attributes.height') . '"
       width="' . $config->get('iframe.attributes.width') . '"
       id="' . $config->get('iframe.attributes.id') . '"
       src="' . $sso_url . '"
       ></iframe>',
      '#context' => [
        'url' => 'url here',
      ],
      '#cache' => [
        'tags' => ['user:' . $user->id()],
        'contexts' => ['user'],
      ],
    ];

    $this->renderer->addCacheableDependency($build, $config);
    $this->renderer->addCacheableDependency($build, $user);

    return $this->renderer->renderPlain($build);
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
      // 'p_passwd' => 'null',
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
   * Validates if parameters set are correct or not.
   *
   * @return bool
   *   If valid, TRUE is returned, else FALSE.
   */
  protected function validateParams(string &$data, string $method) {
    if ($data != NULL && $method != NULL) {
      // Check if padding needs to be added or not.
      if ($pad = (32 - (strlen($data) % 32))) {
        $this->addPadding($data, $pad);
      }
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Adds 0 Padding.
   */
  protected function addPadding(string &$data, string $multiplier) {
    $data .= '&';
    $multiplier--;
    $data .= str_repeat(0, $multiplier);
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
    if ($this->validateParams($data, $method)) {
      // Get the initialization vector.
      $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
      return trim(openssl_encrypt($data, $method, $key, OPENSSL_ZERO_PADDING, $iv));
    }
    else {
      throw new \Exception('Invlid params!');
    }
  }

}
