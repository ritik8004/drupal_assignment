<?php

namespace Drupal\alshaya_spc\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\alshaya_spc\Helper\AlshayaSpcHelper;
use Drupal\alshaya_spc\Event\UserLoginEvent;

/**
 * Hooks into the user login event to perform authentication with Magento API.
 *
 * @package Drupal\alshaya_spc\EventSubscriber
 */
class UserLoginSubscriber implements EventSubscriberInterface {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcHelper
   */
  protected $alshayaSpcHelper;

  /**
   * AlshayaSpcCustomerController constructor.
   *
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcHelper $alshaya_spc_helper
   *   SPC Helper.
   */
  public function __construct(
    AlshayaSpcHelper $alshaya_spc_helper
  ) {
    $this->alshayaSpcHelper = $alshaya_spc_helper;
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
    $email = $event->account->getEmail();
    $this->alshayaSpcHelper->authenticateCustomerBySocialDetail($email);
  }

}
