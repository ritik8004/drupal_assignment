<?php

namespace Drupal\alshaya_behat\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Used to set session data for Behat.
 */
class AlshayaBehatResponseSubscriber implements EventSubscriberInterface {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * Constructs a new RouteNameResponseSubscriber.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Used to get request stack.
   */
  public function __construct(
    RequestStack $request_stack,
  ) {
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Sets a session variable when a Behat request starts.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $session = $request->getSession();
    if ($request->get('behat')) {
      $session->set('is_behat_session', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['onRequest', 30];
    return $events;
  }

}
