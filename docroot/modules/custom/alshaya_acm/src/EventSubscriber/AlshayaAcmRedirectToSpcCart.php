<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Alshaya Acm Redirect To SpcCart.
 */
class AlshayaAcmRedirectToSpcCart implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * AlshayaAcmRedirectToSpcCart constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(RouteMatchInterface $route_match,
    MessengerInterface $messenger) {
    $this->routeMatch = $route_match;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['onRequest'];
    return $events;
  }

  /**
   * Redirects user to basket page with message from acm checkout pages.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Request event object.
   */
  public function onRequest(GetResponseEvent $event) {
    // If acm checkout pages.
    if ($this->routeMatch->getRouteName() == 'acq_checkout.form') {
      $this->messenger->addStatus($this->t('We have improved the checkout experience, please review your bag to proceed.'));
      $response = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString());
      $event->setResponse($response);
    }
  }

}
