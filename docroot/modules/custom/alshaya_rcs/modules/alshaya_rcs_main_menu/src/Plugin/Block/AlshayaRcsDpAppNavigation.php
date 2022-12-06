<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alshaya rcs dp app navigation block.
 *
 * @Block(
 *   id = "alshaya_rcs_dp_app_navigation",
 *   admin_label = @Translation("Alshaya Rcs Dp App Navigation")
 * )
 */
class AlshayaRcsDpAppNavigation extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructor for AlshayaRcsDpAppNavigation.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   */
  public function __construct(array $configuration,
                                    $plugin_id,
                                    $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
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
      $container->get('module_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [];

    $node = _alshaya_advanced_page_get_department_node();
    // If department page, only then process further.
    if ($node instanceof NodeInterface) {
      $data = [
        'name' => '#rcs.appNav.name#',
        'path' => '#rcs.appNav.url_path#',
        'class' => '#rcs.appNav.classes#',
      ];
    }

    $variables = [];
    $variables['category_id'] = $this->configFactory->get('alshaya_rcs_main_menu.settings')->get('root_category');

    // Allow other modules to modify the variables.
    $this->moduleHandler->alter('alshaya_rcs_main_menu', $variables);

    return [
      '#theme' => 'alshaya_rcs_dp_app_navigation',
      '#data' => $data,
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-app_navigation',
            'data-param-entity-to-get' => 'navigation_menu',
            'data-param-category_id' => $variables['category_id'],
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['super_category']);
  }

}
