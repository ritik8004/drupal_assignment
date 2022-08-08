<?php

namespace Drupal\alshaya_advanced_page\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
  protected $routeMatch;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ExternalHreflangGetCurrentUrlEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route matcher.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    ModuleHandlerInterface $module_handler) {
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
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
    if ($this->moduleHandler->moduleExists('alshaya_rcs_main_menu')
      && !empty($route)
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
