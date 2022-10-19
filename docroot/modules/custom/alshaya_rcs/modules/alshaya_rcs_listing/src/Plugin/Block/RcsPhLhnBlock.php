<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dynamic LHN Block for commerce pages.
 *
 * @Block(
 *   id = "rcs_ph_lhn",
 *   admin_label = @Translation("RCS Placeholders LHN"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhLhnBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config to enable/disable the lhn category tree.
   */
  public const ENABLE_DISABLE_CONFIG_KEY = 'alshaya_acm_product_category.settings';

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaCategoryLhnBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    RouteMatchInterface $route_match,
    ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('module_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $variables = [];

    // Add the default category id.
    $variables['category_id'] = $this->configFactory->get('alshaya_rcs_main_menu.settings')->get('root_category');

    // Allow other modules to modify the variables.
    $this->moduleHandler->alter('alshaya_rcs_main_menu', $variables);

    $build['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'rcs-ph-lhn_block',
        'data-param-get-data' => 'false',
        'class' => ['block-alshaya-category-lhn-block'],
        'data-param-entity-to-get' => 'navigation_menu',
        'data-param-category_id' => $variables['category_id'],
      ],
    ];

    // Attach the Listing rendrer library.
    $build['#attached']['library'][] = 'alshaya_rcs_listing/renderer';
    $build['#attached']['library'][] = 'alshaya_rcs_listing/lhn_menu';
    $build['#attached']['library'][] = 'alshaya_rcs_listing/category_utility';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    // By default other pages ( where this is placed ) should have access to it.
    $result = AccessResult::allowed();
    $config = $this->configFactory->get(self::ENABLE_DISABLE_CONFIG_KEY);
    // Not allow if lhn is disabled and it's rcs_category page.
    if ($this->routeMatch->getRouteName() == 'entity.taxonomy_term.canonical') {
      $term = $this->routeMatch->getParameter('taxonomy_term');
      $result = $term->bundle() == 'rcs_category' ?
        AccessResult::allowedif($config->get('enable_lhn_tree')) :
        $result;
    }
    return $result;
  }

}
