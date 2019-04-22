<?php

namespace Drupal\alshaya_acm_product_category\EventSubscriber;

use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaAdvancedPageEventSubscriber.
 */
class ProductCategoryRequestSubscriber implements EventSubscriberInterface {

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * AlshayaAdvancedPageEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route matcher.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * Check for term route and throw exception based on field_commerce_status.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onRequest(GetResponseEvent $event) {
    $request = $event->getRequest();

    // If we've got an exception, nothing to do here.
    if ($request->get('exception') != NULL
        || $this->routeMatch->getRouteName() !== 'entity.taxonomy_term.canonical') {
      return;
    }

    if (($taxonomy_term = $this->routeMatch->getParameter('taxonomy_term')) && $taxonomy_term instanceof TermInterface) {
      if ($taxonomy_term->bundle() !== 'acq_product_category') {
        return;
      }

      if ($taxonomy_term->get('field_override_target_link')->value == '1') {
        $qs = $request->getQueryString();
        if ($qs) {
          $qs = '?' . $qs;
        }

        $path = Url::fromUri($taxonomy_term->get('field_target_link')->getString())->toString();
        $event->setResponse(new CacheableRedirectResponse($request->getUriForPath($path) . $qs));
      }

      if ($taxonomy_term->get('field_commerce_status')->getString() !== '1') {
        throw new NotFoundHttpException();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 20];
    return $events;
  }

}
