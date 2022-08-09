<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the alshaya rcs main menu config object.
    $rcs_main_menu_settings = $this->configFactory->get('alshaya_rcs_main_menu.settings');
    $max_depth = $rcs_main_menu_settings->get('menu_max_depth');
    $category_id = $rcs_main_menu_settings->get('root_category');

    // Get the alshaya main menu config object.
    $main_menu_settings = $this->configFactory->get('alshaya_main_menu.settings');
    $highlight_timing = (int) $main_menu_settings->get('desktop_main_menu_highlight_timing');
    $max_nb_col = (int) $main_menu_settings->get('max_nb_col');
    $ideal_max_col_length = (int) $main_menu_settings->get('ideal_max_col_length');
    $menu_layout = $main_menu_settings->get('desktop_main_menu_layout');

    // Get the alshaya mobile main menu config object.
    $mobile_menu_settings = $this->configFactory->get('alshaya_main_menu.settings');
    $mobile_menu_max_depth = $mobile_menu_settings->get('mobile_main_menu_max_depth');

    // Get Super category status.
    $super_category_status = (boolean) $this->configFactory->get('alshaya_super_category.settings')->get('status');
    if ($super_category_status) {
      // Increase the mobile max depth.
      $mobile_menu_max_depth++;
    }

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
            'data-param-category_id' => $category_id,
          ],
        ],
      ],
      '#attached' => [
        'drupalSettings' => [
          'alshayaRcs' => [
            'navigationMenu' => [
              'menuMaxDepth' => $max_depth,
              'mobileMenuMaxDepth' => $mobile_menu_max_depth,
              'maxNbCol' => $max_nb_col > 0 ? $max_nb_col : 6,
              'idealMaxColLength' => $ideal_max_col_length > 0 ? $ideal_max_col_length : 10,
              'menuLayout' => $menu_layout,
              'highlightTiming' => $highlight_timing,
            ],
          ],
        ],
        'library' => [
          'alshaya_rcs_main_menu/renderer',
          'alshaya_rcs_main_menu/main_menu_level1',
          'alshaya_rcs_main_menu/main_menu_level2_partial',
          'alshaya_white_label/rcs-ph-navigation-menu',
        ],
      ],
      '#cache' => [
        'tags' => array_merge(
          $this->getCacheTags(),
          $rcs_main_menu_settings->getCacheTags(),
          $main_menu_settings->getCacheTags()
        ),
      ],
    ];

    // Attach further Handlebars templates if needed.
    if ($max_depth > 2) {
      $build['#attached']['library'][] = 'alshaya_rcs_main_menu/main_menu_level3_partial';
    }
    if ($max_depth > 3) {
      $build['#attached']['library'][] = 'alshaya_rcs_main_menu/main_menu_level4_partial';
    }

    return $build;
  }

}
