<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Breadcrumb\BreadcrumbManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dynamic breadcrumb for commerce pages.
 *
 * @Block(
 *   id = "rcs_ph_breadcrumb",
 *   admin_label = @Translation("RCS Placeholders breadcrumb"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhBreadcrumb extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Breadcrumb manager object.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbManager
   */
  protected $breadcrumbManager;

  /**
   * Current route object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRoute;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BreadcrumbManager $breadcrumb_manager, RouteMatchInterface $current_route) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->breadcrumbManager = $breadcrumb_manager;
    $this->currentRoute = $current_route;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('breadcrumb'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'rcs-ph-breadcrumb',
        'data-param-get-data' => 'false',
      ],
    ];

    $build['wrapper']['content'] = [
      '#theme' => 'breadcrumb',
      '#links' => $this->breadcrumbManager->build($this->currentRoute)->getLinks(),
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Vary based on the route.
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'route',
    ]);
  }

}
