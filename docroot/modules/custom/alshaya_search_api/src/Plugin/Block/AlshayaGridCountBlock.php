<?php

namespace Drupal\alshaya_search_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom block which grid buttons and facet count.
 *
 * @Block(
 *  id = "alshaya_grid_count_block",
 *  admin_label = @Translation("Alshaya Grid/Count block"),
 * )
 */
class AlshayaGridCountBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * AlshayaGridCountBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'grid_count_block',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $is_srp_promo_plp_page = FALSE;
    $route_name = $this->routeMatch->getRouteName();
    $routes = [
      'entity.taxonomy_term.canonical',
      'entity.node.canonical',
      'view.search.page',
    ];

    // If PLP/SRP/Promo Page.
    if (in_array($route_name, $routes)) {
      $is_srp_promo_plp_page = TRUE;
      // If PLP page, then check for department page and not show there.
      if ($route_name == 'entity.node.canonical') {
        $node = $this->routeMatch->getParameter('node');
        $department_pages = alshaya_advanced_page_get_pages();
        $is_srp_promo_plp_page = !in_array($node->id(), $department_pages);
      }
    }

    return AccessResult::allowedif($is_srp_promo_plp_page);
  }

}
