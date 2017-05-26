<?php

namespace Drupal\alshaya_master\EventSubscriber;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;

/**
 * Access Denied Exception subscriber.
 */
class Alshaya403ExceptionSubscriber extends HttpExceptionSubscriberBase {

  /**
   * The current account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Alshaya403ExceptionSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current account object.
   */
  public function __construct(AccountProxyInterface $account) {
    $this->currentUser = $account;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    // A very high priority so that it can take precedent over anything else.
    return 200;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return ['html'];
  }

  /**
   * Handles a 403 error for HTML.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The event to process.
   */
  public function on403(GetResponseForExceptionEvent $event) {
    $url = '/user/login';
    // If user is logged-in redirect to dashboard.
    if ($this->currentUser->isAuthenticated()) {
      $uid = $this->currentUser->id();
      $url = '/user/' . $uid;
    }

    $response = new TrustedRedirectResponse($url);
    $event->setResponse($response);
  }

}
