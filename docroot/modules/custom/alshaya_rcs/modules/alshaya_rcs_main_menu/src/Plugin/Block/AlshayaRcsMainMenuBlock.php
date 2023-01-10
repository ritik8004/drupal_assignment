<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_main_menu\Form\AlshayaMainMenuConfigForm;

/**
 * Provides alshaya rcs main menu block.
 *
 * @Block(
 *   id = "alshaya_rcs_main_menu",
 *   admin_label = @Translation("Alshaya rcs main menu")
 * )
 */
class AlshayaRcsMainMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
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
   * AlshayaRcsMainMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler
  ) {
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
    // Get the alshaya rcs main menu config object.
    $rcs_main_menu_settings = $this->configFactory->get('alshaya_rcs_main_menu.settings');
    $variables = [];
    $variables['max_depth'] = $rcs_main_menu_settings->get('menu_max_depth');
    $variables['category_id'] = $rcs_main_menu_settings->get('root_category');

    // Get the alshaya main menu config object.
    $main_menu_settings = $this->configFactory->get('alshaya_main_menu.settings');
    $variables['highlight_timing'] = (int) $main_menu_settings->get('desktop_main_menu_highlight_timing');
    $variables['max_nb_col'] = (int) $main_menu_settings->get('max_nb_col');
    $variables['ideal_max_col_length'] = (int) $main_menu_settings->get('ideal_max_col_length');
    $variables['menu_layout'] = $main_menu_settings->get('desktop_main_menu_layout');
    $variables['show_l2_in_separate_column'] = $main_menu_settings->get('show_l2_in_separate_column');

    // Get the alshaya mobile main menu config object.
    $mobile_menu_settings = $this->configFactory->get('alshaya_main_menu.settings');
    $variables['mobile_menu_max_depth'] = $mobile_menu_settings->get('mobile_main_menu_max_depth');
    $variables['mobile_menu_layout'] = $mobile_menu_settings->get('mobile_main_menu_layout');

    // Get Super category status.
    $super_category_status = (boolean) $this->configFactory->get('alshaya_super_category.settings')->get('status');
    if ($super_category_status) {
      // Increase the mobile max depth.
      $variables['mobile_menu_max_depth']++;
    }

    // Allow other modules to modify the variables.
    $this->moduleHandler->alter('alshaya_rcs_main_menu', $variables);

    $build = [
      '#attributes' => [
        'class' => [
          'block-alshaya-main-menu',
        ],
      ],
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-navigation_menu',
            'data-rcs-dependency' => 'none',
            'data-param-category_id' => $variables['category_id'],
          ],
        ],
      ],
      '#attached' => [
        'drupalSettings' => [
          'alshayaRcs' => [
            'navigationMenu' => [
              'menuMaxDepth' => $variables['max_depth'],
              'mobileMenuMaxDepth' => $variables['mobile_menu_max_depth'],
              'maxNbCol' => $variables['max_nb_col'] > 0 ? $variables['max_nb_col'] : 6,
              'idealMaxColLength' => $variables['ideal_max_col_length'] > 0 ? $variables['ideal_max_col_length'] : 10,
              'menuLayout' => $variables['menu_layout'],
              'highlightTiming' => $variables['highlight_timing'],
              'mobileMenuLayout' => $variables['mobile_menu_layout'],
              'showL2InSeparateColumn' => $variables['show_l2_in_separate_column'],
            ],
          ],
        ],
        'library' => [
          'alshaya_rcs_main_menu/renderer',
          'alshaya_rcs_main_menu/main_menu_level1',
          'alshaya_rcs_main_menu/main_menu_level2_partial',
          'alshaya_rcs_main_menu/view_all',
          'alshaya_white_label/rcs-ph-navigation-menu',
        ],
      ],
      '#cache' => [
        'tags' => array_merge(
          $this->getCacheTags(),
          $rcs_main_menu_settings->getCacheTags(),
          $main_menu_settings->getCacheTags()
        ),
        'contexts' => ['url.path'],
      ],
    ];

    // Attach further Handlebars templates if needed.
    if ($variables['max_depth'] > 2) {
      $build['#attached']['library'][] = 'alshaya_rcs_main_menu/main_menu_level3_partial';
    }
    if ($variables['max_depth'] > 3) {
      $build['#attached']['library'][] = 'alshaya_rcs_main_menu/main_menu_level4_partial';
    }

    if ($variables['mobile_menu_layout'] === AlshayaMainMenuConfigForm::MOBILE_MENU_VISUAL) {
      $build['#attached']['library'][] = 'alshaya_rcs_main_menu/visual_mobile_menu_ui';
      $build['#attached']['library'][] = 'alshaya_rcs_main_menu/visual_mobile_menu_level1_partial';
      $build['#attached']['library'][] = 'alshaya_rcs_main_menu/visual_mobile_menu_level2_partial';
      if ($variables['max_depth'] > 2) {
        $build['#attached']['library'][] = 'alshaya_rcs_main_menu/visual_mobile_menu_level3_partial';
      }
      $build['#attached']['library'][] = 'alshaya_rcs_main_menu/visual_mobile_menu_carousel_partial';
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['super_category']);
  }

}
