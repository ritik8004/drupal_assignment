<?php

namespace Drupal\alshaya_spc\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\alshaya_spc\Event\UserLoginEvent;
use Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper;

/**
 * Hooks into the user login event to perform authentication with Magento API.
 *
 * @package Drupal\alshaya_spc\EventSubscriber
 */
class UserLoginSubscriber implements EventSubscriberInterface {

  /**
   * SPC customer helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper
   */
  protected $spcCustomerHelper;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * AlshayaSpcCustomerController constructor.
   *
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper $spc_customer_helper
   *   SPC customer helper.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config factory manager.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   */
  public function __construct(
    AlshayaSpcCustomerHelper $spc_customer_helper,
    ConfigFactory $configFactory,
    SessionInterface $session
  ) {
    $this->spcCustomerHelper = $spc_customer_helper;
    $this->configFactory = $configFactory;
    $this->session = $session;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserLoginEvent::EVENT_NAME => 'onUserLogin',
    ];
  }

  /**
   * Subscribe to the user login event dispatched.
   *
   * @param \Drupal\alshaya_spc\Event\UserLoginEvent $event
   *   The event.
   */
  public function onUserLogin(UserLoginEvent $event) {
    $version = $this->configFactory->get('alshaya_acm.cart_config')->get('version');
    // Check if its V2.
    if ($version == 2) {
      // Get customer email.
      $email = $event->account->getEmail();

      // Login with Magento.
      $response = $this->spcCustomerHelper->authenticateCustomerBySocialDetail($email);

      // Store bearer token on secure cookie.
      if (is_string($response)) {

        // Store token in the session.
        $this->session->set('alshaya_spc_token', json_decode($response));
      }
    }
  }

}
