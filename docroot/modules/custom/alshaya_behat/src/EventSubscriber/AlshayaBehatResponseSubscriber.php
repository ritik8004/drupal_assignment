<?php

namespace Drupal\alshaya_behat\EventSubscriber;

use Drupal\Core\Site\Settings;
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
   * Drupal settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructs a new RouteNameResponseSubscriber.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Used to get request stack.
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal site settings object.
   */
  public function __construct(
    RequestStack $request_stack,
    Settings $settings,
  ) {
    $this->request = $request_stack->getCurrentRequest();
    $this->settings = $settings;
  }

  /**
   * Sets a session variable when a Behat request starts.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onRequest(RequestEvent $event) {
    // Check if functionality is disabled.
    if ($this->settings::get('alshaya_behat_disabled', FALSE)) {
      return;
    }

    // Check if there is behat argument in the query string
    // and it matches the secret key.
    $request = $event->getRequest();
    if ($request->get('behat')
      && $request->get('behat') === $this->settings->get('behat_secret_key')
    ) {
      $session = $request->getSession();
      // Flag it as behat session.
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
