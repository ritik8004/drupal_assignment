<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides alshaya rcs shop by menu block.
 *
 * @Block(
 *   id = "alshaya_shop_by_menu",
 *   admin_label = @Translation("Alshaya rcs shop by menu")
 * )
 */
class AlshayaRcsShopByMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaMegaMenuBlock constructor.
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
   *   Module Handler service object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
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
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Prepare field icon placeholder array.
    $icon = [
      'url' => '#rcs.shopbymenuItem.icon_url#',
      'label' => '#rcs.shopbymenuItem.name#',
    ];

    // Prepare a static term array with placeholders
    // for all the possible combinations.
    $term_data = [
      '1' => [
        // 1st Level item with clickable and enabled for both mobile an desktop.
        'id' => '1',
        'label' => '#rcs.shopbymenuItem.name#',
        'path' => '#rcs.shopbymenuItem.url_path#',
        'clickable' => "1",
        'depth' => 1,
        'move_to_right' => FALSE,
        'highlight_paragraph' => [],
        'class' => ['level-1', 'clickable'],
      ],
    ];

    // Get the alshaya rcs main menu config object.
    $alshaya_rcs_main_menu_settings = $this->configFactory->get('alshaya_rcs_main_menu.settings');
    $menu_max_depth = $alshaya_rcs_main_menu_settings->get('menu_max_depth');
    // Set default parent_id 0 to load first level category terms.
    $parent_id = 0;
    $context = ['block' => $this->getBaseId()];

    // Get the alshaya main menu config object.
    $alshaya_main_menu_settings = $this->configFactory->get('alshaya_main_menu.settings');

    $desktop_main_menu_layout = $alshaya_main_menu_settings->get('desktop_main_menu_layout');
    if ($desktop_main_menu_layout == 'default' || $desktop_main_menu_layout == 'menu_dynamic_display') {
      $columns_tree = $this->getColumnDataMenuAlgo($term_data);
      $this->moduleHandler->alter('alshaya_main_menu_links', $columns_tree, $parent_id, $context);
    }
    else {
      $this->moduleHandler->alter('alshaya_main_menu_links', $term_data, $parent_id, $context);
    }

    $highlight_timing = (int) $alshaya_main_menu_settings->get('desktop_main_menu_highlight_timing');
    $max_nb_col = (int) $alshaya_main_menu_settings->get('max_nb_col');
    $ideal_max_col_length = (int) $alshaya_main_menu_settings->get('ideal_max_col_length');

    // Return render array with all block elements.
    return [
      '#theme' => 'alshaya_main_menu_level1',
      '#term_tree' => $term_data,
      '#column_tree' => $columns_tree ?? [],
      '#menu_type' => $desktop_main_menu_layout,
      '#attributes' => [
        'class' => [
          'block-alshaya-main-menu',
        ],
      ],
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-shop_by_menu',
            'data-rcs-dependency' => 'none',
          ],
        ],
      ],
      '#attached' => [
        // Pass in drupal settings for FE.
        'drupalSettings' => [
          'alshayaRcs' => [
            'navigationMenu' => [
              'rootCategory' => $alshaya_rcs_main_menu_settings->get('root_category'),
              'menuMaxDepth' => 1,
              'query' => $this->getRcsCategoryMenuQuery($menu_max_depth),
              'maxNbCol' => 1,
              'idealMaxColLength' => $ideal_max_col_length > 0 ? $ideal_max_col_length : 10,
              'menuLayout' => $desktop_main_menu_layout,
            ],
          ],
        ],
        // Attach required JS libraries.
        'library' => [
          'alshaya_rcs_main_menu/renderer',
        ],
      ],
      '#cache' => [
        'tags' => $alshaya_rcs_main_menu_settings->getCacheTags(),
      ],
    ];

  }

  /**
   * Helper func to re-structure term array same as in V1.
   *
   * @param array $term_data
   *   Array for taxonomy term data.
   *
   * @return array
   *   Column tree array with the same structure as V1.
   */
  protected function getColumnDataMenuAlgo(array $term_data) {
    $columns_tree = [];
    foreach ($term_data as $l2s) {
      $ideal_max_col_length = 10;

      $columns = [];
      $col = 0;
      $col_total = 0;

      $columns_tree[$l2s['label']] = [
        'l1_object' => $l2s,
        'columns' => $columns,
      ];
    }
    return $columns_tree;
  }

  /**
   * Helper function to build the graphql query dynamically.
   *
   * @param int $depth
   *   Define the depth of the query.
   *
   * @return string
   *   The graphql query to fetch data using API.
   */
  protected function getRcsCategoryMenuQuery($depth = 0) {
    $fieldsToFetch = 'children_count
        children {
          id
          level
          name
          meta_title
          include_in_menu
          url_path
          url_key
          show_on_dpt
          position
          is_anchor
          display_view_all
          ';

    if ($depth > 0) {
      $fieldsToFetch .= $this->getRcsCategoryMenuQuery($depth - 1);
    }
    $fieldsToFetch .= '}';
    return $fieldsToFetch;
  }

}
