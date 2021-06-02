<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\Core\Session\AccountInterface;

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
   * SPC customer helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper
   */
  protected $spcCustomerHelper;

  /**
   * Constructor for AlshayaSpcHelper.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper $spc_customer_helper
   *   SPC Helper.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    SessionInterface $session,
    AccountInterface $current_user,
    AlshayaSpcCustomerHelper $spc_customer_helper
  ) {
    $this->configFactory = $config_factory;
    $this->session = $session;
    $this->currentUser = $current_user;
    $this->spcCustomerHelper = $spc_customer_helper;
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
   * Authenticate on Magento using email and stores in the session.
   *
   * @param string $email
   *   The email address.
   */
  public function authenticateCustomerBySocialDetail($email) {
    if (!$this->currentUser->isAuthenticated()) {
      return;
    }

    if ($this->getCommerceBackendVersion() == 2) {
      // Login with Magento.
      $response = $this->spcCustomerHelper->authenticateCustomerBySocialDetail($email);
      if ($token = json_decode($response)) {
        // Store bearer token on secure cookie.
        $this->session->set('alshaya_spc_token', $token);
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
    $token = $this->session->get('alshaya_spc_token');
    if (is_string($token)) {
      return $token;
    }
    else {
      $email = $this->currentUser->getEmail();
      return $this->authenticateCustomerBySocialDetail($email);
    }
  }

}
