<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rcs_placeholders\Service\RcsPhHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

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
   * Stores the rcs placeholder helper.
   *
   * @var \Drupal\rcs_placeholders\Service\RcsPhHelperInterface
   */
  protected $rcsPhHelper;

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
   * @param \Drupal\rcs_placeholders\Service\RcsPhHelperInterface $rcs_ph_helper
   *   Rcs placeholders helper service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, RcsPhHelperInterface $rcs_ph_helper, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->rcsPhHelper = $rcs_ph_helper;
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
      $container->get('rcs_placeholders.helper'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the rcs placeholder terms data.
    $ph_term_data = $this->rcsPhHelper->getRcsPhCategoryTermData();

    // If no data, no need to render the block.
    if (empty($ph_term_data)) {
      return [];
    }

    // Get the placeholder term name.
    $ph_term_label = $ph_term_data->name->value;

    // Prepare a static term array with placeholders
    // for all the possible combinations.
    $term_data = [
      '1' => [
        // 1st Level item with clickable and enabled for both mobile an desktop.
        'id' => '1',
        'label' => $ph_term_label,
        'path' => '#rcs.category.url_path#',
        'clickable' => "1",
        'depth' => 1,
        'move_to_right' => FALSE,
        'highlight_paragraph' => [],
        'class' => ['level-1', 'clickable'],
        'child' => [
          '2' => [
            // 2nd Level item non-clickable and
            // enabled for both mobile an desktop.
            'id' => '2',
            'label' => $ph_term_label,
            'path' => '#rcs.category.url_path#',
            'clickable' => "1",
            'depth' => 2,
            'move_to_right' => FALSE,
            'class' => ['level-2', 'clickable'],
            'child' => [
              '3' => [
                // 3rd Level item with clickable and
                // enabled for both mobile an desktop.
                'id' => '3',
                'label' => $ph_term_label,
                'path' => '#rcs.category.url_path#',
                'clickable' => "1",
                'depth' => 3,
                'move_to_right' => FALSE,
                'class' => ['level-3', 'clickable'],
                'child' => [],
              ],
              '4' => [
                // 3rd Level item with clickable and
                // enabled for both mobile an desktop.
                'id' => '4',
                'label' => $ph_term_label,
                'path' => '#rcs.category.url_path#',
                'clickable' => "0",
                'depth' => 3,
                'move_to_right' => FALSE,
                'class' => ['level-3', 'non-clickable'],
                'child' => [],
              ],
            ],
          ],
          '5' => [
            // 2nd Level item non-clickable and enabled for mobile only.
            'id' => '5',
            'label' => $ph_term_label,
            'path' => '#rcs.category.url_path#',
            'clickable' => "0",
            'depth' => 2,
            'move_to_right' => FALSE,
            'class' => ['level-2', 'non-clickable'],
            'child' => [
              '6' => [
                // 3rd Level item with clickable and
                // enabled for both mobile an desktop.
                'id' => '6',
                'label' => $ph_term_label,
                'path' => '#rcs.category.url_path#',
                'clickable' => "1",
                'depth' => 3,
                'move_to_right' => FALSE,
                'class' => ['level-3', 'clickable'],
                'child' => [],
              ],
              '7' => [
                // 3rd Level item with clickable and
                // enabled for both mobile an desktop.
                'id' => '7',
                'label' => $ph_term_label,
                'path' => '#rcs.category.url_path#',
                'clickable' => "0",
                'depth' => 3,
                'move_to_right' => FALSE,
                'class' => ['level-3', 'non-clickable'],
                'child' => [],
              ],
            ],
          ],
        ],
      ],
      '8' => [
        // 1st Level item non-clickable and enabled for both mobile an desktop.
        'id' => '8',
        'label' => '#rcs.category.name1#',
        'path' => '#rcs.category.url_path#',
        'clickable' => "0",
        'depth' => 1,
        'move_to_right' => FALSE,
        'highlight_paragraph' => [],
        'class' => ['level-1', 'non-clickable'],
        'child' => [
          '5' => [
            // 2nd Level item non-clickable and
            // enabled for both mobile an desktop.
            'id' => '5',
            'label' => $ph_term_label,
            'path' => '#rcs.category.url_path#',
            'clickable' => "1",
            'depth' => 2,
            'move_to_right' => FALSE,
            'class' => ['level-2', 'clickable'],
            'child' => [],
          ],
        ],
      ],
    ];

    // Get the alshaya rcs main menu config object.
    $alshaya_rcs_main_menu_settings = $this->configFactory->get('alshaya_rcs_main_menu.settings');
    $menu_max_depth = $alshaya_rcs_main_menu_settings->get('menu_max_depth');
    if ($menu_max_depth > 3) {
      $term_data[1]['child'][2]['child'][3]['child'] = [
        '9' => [
        // 4th Level item with clickable and
        // enabled for both mobile an desktop.
          'id' => '9',
          'label' => $ph_term_label,
          'path' => '#rcs.category.url_path#',
          'clickable' => "1",
          'depth' => 4,
          'move_to_right' => FALSE,
          'class' => ['level-4', 'clickable'],
          'child' => [],
        ],
      ];
    }

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
      '#settings' => [
        'desktop_main_menu_highlight_timing' => $highlight_timing,
      ],
      '#term_tree' => $term_data,
      '#column_tree' => $columns_tree ?? [],
      '#menu_type' => $desktop_main_menu_layout,
      '#attributes' => [
        'class' => [
          'block-alshaya-main-menu',
        ],
      ],
      // @todo need to move prefix and suffix in container type.
      '#prefix' => '<div id="rcs-ph-navigation_menu">',
      '#suffix' => '</div>',
      '#attached' => [
        // Pass in drupal settings for FE.
        'drupalSettings' => [
          'alshayaRcs' => [
            'navigationMenu' => [
              'rootCategory' => $alshaya_rcs_main_menu_settings->get('root_category'),
              'menuMaxDepth' => $menu_max_depth,
              'query' => $this->getRcsCategoryMenuQuery($menu_max_depth),
              'maxNbCol' => $max_nb_col > 0 ? $max_nb_col : 6,
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
   * Column data after menu algo is applied.
   */
  public function getColumnDataMenuAlgo($term_data) {
    $columns_tree = [];
    foreach ($term_data as $l2s) {
      $max_nb_col = (int) $this->configFactory->get('alshaya_main_menu.settings')->get('max_nb_col');
      $ideal_max_col_length = (int) $this->configFactory->get('alshaya_main_menu.settings')->get('ideal_max_col_length');
      $max_nb_col = $max_nb_col > 0 ? $max_nb_col : 6;
      $ideal_max_col_length = $ideal_max_col_length > 0 ? $ideal_max_col_length : 10;

      do {
        $columns = [];
        $col = 0;
        $col_total = 0;
        $reprocess = FALSE;

        foreach ($l2s['child'] as $l3s) {
          // 2 below means L2 item + one blank line for spacing).
          $l2_cost = 2 + count($l3s['child']);

          // If we are detecting a longer column than the expected size
          // we iterate with new max.
          if ($l2_cost > $ideal_max_col_length) {
            $ideal_max_col_length = $l2_cost;
            $reprocess = TRUE;
            break;
          }

          if ($col_total + $l2_cost > $ideal_max_col_length) {
            $col++;
            $col_total = 0;
          }

          // If we have too many columns we try with more items per column.
          if ($col >= $max_nb_col) {
            $ideal_max_col_length++;
            break;
          }

          $columns[$col][] = $l3s;

          $col_total += $l2_cost;

        }
      } while ($reprocess || $col >= $max_nb_col);
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
  public function getRcsCategoryMenuQuery($depth = 0) {
    $fieldsToFetch = 'children_count
        children {
          id
          level
          name
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
