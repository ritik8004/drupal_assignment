<?php

namespace Drupal\alshaya_options_list\Routing;

use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
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
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface service.
   * @param Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya options service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AlshayaOptionsListHelper $alshaya_options_service) {
    $this->config = $config_factory->get('alshaya_options_list.settings');
    $this->alshayaOptionsService = $alshaya_options_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('alshaya_options_list.alshaya_options_service')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\RouteCollection|null
   *   An array of route objects.
   */
  public function routes() {
    $route_collection = new RouteCollection();

    if (!$this->alshayaOptionsService->optionsPageEnabled()) {
      return $route_collection;
    }

    $pages = $this->config->get('alshaya_options_pages');
    foreach ($pages ?? [] as $page) {
      $route = new Route(
        '/' . $page['url'],
        [
          '_controller' => '\Drupal\alshaya_options_list\Controller\AlshayaOptionsPageController::optionsPage',
          '_title' => $page['title'],
        ],
        ['_access' => 'TRUE']
      );

      $route_name = 'alshaya_options_list.options_page' . str_replace('/', '-', $page['url']);
      $route_collection->add($route_name, $route);
    }

    return $route_collection;
  }

}
