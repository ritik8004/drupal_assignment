<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\alshaya_api\AlshayaApiWrapper;

/**
 * Class containing general helper methods for SPC.
 */
class AlshayaSpcHelper {

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Constructor for AlshayaSpcHelper.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   The api wrapper.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    SessionInterface $session,
    AccountInterface $current_user,
    AlshayaApiWrapper $api_wrapper
  ) {
    $this->configFactory = $config_factory;
    $this->session = $session;
    $this->currentUser = $current_user;
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * Gets the commerce backend version.
   *
   * @return string
   *   The commerce backend verion.
   */
  public function getCommerceBackendVersion() {
    return $this->configFactory->get('alshaya_acm.cart_config')->get('version') ?? 1;
  }

  /**
   * Helper function to get a token from Magento using Social Details.
   *
   * @param string $mail
   *   The email address.
   */
  public function getCustomerTokenBySocialDetail($mail) {
    if (!$this->currentUser->isAuthenticated()) {
      return;
    }

    if ($this->getCommerceBackendVersion() == 2) {
      $response = $this->apiWrapper->getCustomerTokenBySocialDetail($mail);
      if ($token = json_decode($response)) {
        $this->session->set('magento_customer_token', $token);
      }
    }
  }

  /**
   * Gets the bearer token from session.
   *
   * If its not in the session, it retrieves from Magento.
   *
   * @return string|null
   *   Then bearer token if available, otherwise NULL.
   */
  public function getBearerToken() {
    $token = $this->session->get('magento_customer_token');
    if (is_string($token)) {
      return $token;
    }
    else {
      $email = $this->currentUser->getEmail();
      return $this->getCustomerTokenBySocialDetail($email);
    }
  }

}
