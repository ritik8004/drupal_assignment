<?php

namespace Drupal\alshaya_behat\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
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
   * Temp storage to keep session values.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStorage;

  /**
   * Constructs a new RouteNameResponseSubscriber.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Used to get request stack.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_storage
   *   Used as temporary storage.
   */
  public function __construct(
    RequestStack $request_stack,
    PrivateTempStoreFactory $temp_storage
  ) {
    $this->request = $request_stack->getCurrentRequest();
    $this->tempStorage = $temp_storage->get('alshaya_behat');
  }

  /**
   * Sets a session variable when a Behat request starts.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onRequest(RequestEvent $event) {
    $request = $event->getRequest();
    if ($request->query->get('behat')) {
      $this->tempStorage->set('is_behat_session', TRUE);
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
