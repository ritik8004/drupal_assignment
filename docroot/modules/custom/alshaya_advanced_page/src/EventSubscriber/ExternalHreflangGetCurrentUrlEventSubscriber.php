<?php

namespace Drupal\alshaya_advanced_page\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\external_hreflang\Event\ExternalHreflangGetCurrentUrlEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class External Hreflang GetCurrentUrl EventSubscriber.
 *
 * @package Drupal\alshaya_advanced_page\EventSubscriber
 */
class ExternalHreflangGetCurrentUrlEventSubscriber implements EventSubscriberInterface {

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * ExternalHreflangGetCurrentUrlEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route matcher.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ExternalHreflangGetCurrentUrlEvent::EVENT_NAME][] = [
      'onGetCurrentUrlEvent',
      100,
    ];
    return $events;
  }

  /**
   * Provide proper url for current page if it is department page.
   *
   * @param \Drupal\external_hreflang\Event\ExternalHreflangGetCurrentUrlEvent $event
   *   Event object.
   */
  public function onGetCurrentUrlEvent(ExternalHreflangGetCurrentUrlEvent $event) {
    $route = $this->routeMatch->getRouteObject();
    // If _department_page_node option is there for V2 then prepare the URL here
    // only otherwise the module will throw route not found exception.
    if (function_exists('alshaya_rcs_main_menu_is_department_page')
    && $route->hasOption('_department_page_node')) {
      $url = Url::fromRoute('entity.node.canonical', [
        'node' => $route->getOption('_department_page_node'),
      ]);
    }
    else {
      if (empty($route) || !($route->hasOption('_department_page_term'))) {
        return;
      }

      $url = Url::fromRoute('entity.taxonomy_term.canonical', [
        'taxonomy_term' => $route->getOption('_department_page_term'),
      ]);
    }

    $event->setCurrentUrl($url);
    $event->stopPropagation();
  }

}
