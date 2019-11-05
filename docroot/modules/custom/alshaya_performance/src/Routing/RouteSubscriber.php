<?php

namespace Drupal\alshaya_performance\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * RouteSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $allowed = $this->getRoutesAllowedInMaintenanceMode();

    // Allow some rest apis to work in maintenance mode.
    foreach ($collection->all() as $key => $route) {
      if (in_array($key, $allowed)) {
        $route->setOption('_maintenance_access', TRUE);
      }
    }
  }

  /**
   * Get the routes allowed in maintenance mode.
   *
   * @return array
   *   Routes allowed in maintenance mode.
   */
  protected function getRoutesAllowedInMaintenanceMode() {
    return $this->configFactory->get('alshaya_performance.settings')->get('routes_allowed_in_maintenance_mode') ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', 0];
    return $events;
  }

}
