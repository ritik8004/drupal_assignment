<?php

namespace Drupal\alshaya_options_list\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines a dynamic path based off of the redirect uri variable.
 */
class AlshayaOptionsListRoutes implements ContainerInjectionInterface {

  /**
   * The Config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('alshaya_options_list.admin_settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   An array of route objects.
   */
  public function routes() {
    $route_collection = new RouteCollection();
    $pages = $this->config->get('alshaya_options_pages');

    foreach ($pages as $page) {
      $route = new Route(
        '/' . $page['url'],
        [
          '_controller' => '\Drupal\alshaya_options_list\Controller\AlshayaOptionsPageController::optionsPage',
        ],
        [
          '_access' => 'TRUE',
        ]
      );
      $route_name = 'alshaya_options_list.options_page' . str_replace('/', '-', $page['url']);
      $route_collection->add($route_name, $route);
    }

    return $route_collection;
  }

}
