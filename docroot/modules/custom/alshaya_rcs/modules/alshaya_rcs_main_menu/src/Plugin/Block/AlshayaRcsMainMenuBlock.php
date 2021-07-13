<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsMenuTree;
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
   * Stores the RCS category menu data.
   *
   * @var \Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsMenuTree
   */
  protected $rcsCategoryMenuData;

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
   * @param \Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsMenuTree $rcs_category_data
   *   Alshaya rcs menu tree service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, AlshayaRcsMenuTree $rcs_category_data, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->rcsCategoryMenuData = $rcs_category_data;
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
      $container->get('alshaya_rcs_main_menu.rcs_category_menu'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the placeholder terms data.
    $phTermData = $this->rcsCategoryMenuData->getRcsCategoryPlaceholderTerm();

    // If no data, no need to render the block.
    if (empty($phTermData)) {
      return [];
    }

    // Get the placeholder term name.
    $phTermLabel = $phTermData->name->value;

    // Prepare a static term array with placeholders
    // for all the possible combinations.
    $term_data = [
      '1' => [
        // 1st Level item with clickable and enabled for both mobile an desktop.
        'id' => '1',
        'label' => $phTermLabel,
        'path' => '#rcs.category.url_path#',
        'clickable' => "1",
        'display_in_desktop' => "1",
        'display_in_mobile' => "1",
        'depth' => 1,
        'move_to_right' => FALSE,
        'highlight_paragraph' => [],
        'child' => [
          '2' => [
            // 2nd Level item with clickable and
            // enabled for both mobile an desktop.
            'id' => '2',
            'label' => $phTermLabel,
            'path' => '#rcs.category.url_path#',
            'clickable' => "1",
            'display_in_desktop' => "1",
            'display_in_mobile' => "1",
            'depth' => 2,
            'move_to_right' => FALSE,
            'highlight_paragraph' => [],
            'child' => [
              '3' => [
                // 3rd Level item with clickable and
                // enabled for both mobile an desktop.
                'id' => '3',
                'label' => $phTermLabel,
                'path' => '#rcs.category.url_path#',
                'clickable' => "1",
                'display_in_desktop' => "1",
                'display_in_mobile' => "1",
                'depth' => 3,
                'move_to_right' => TRUE,
                'highlight_paragraph' => [],
                'child' => [],
              ],
            ],
          ],
          '5' => [
            // 2nd Level item non-clickable and
            // enabled for both mobile an desktop.
            'id' => '5',
            'label' => $phTermLabel,
            'path' => '#rcs.category.url_path#',
            'clickable' => "0",
            'display_in_desktop' => "1",
            'display_in_mobile' => "1",
            'depth' => 2,
            'move_to_right' => FALSE,
            'highlight_paragraph' => [],
            'child' => [],
          ],
          '6' => [
            // 2nd Level item non-clickable and enabled for mobile only.
            'id' => '6',
            'label' => $phTermLabel,
            'path' => '#rcs.category.url_path#',
            'clickable' => "0",
            'display_in_desktop' => "0",
            'display_in_mobile' => "1",
            'depth' => 2,
            'move_to_right' => TRUE,
            'highlight_paragraph' => [],
            'child' => [],
          ],
          '7' => [
            // 2nd Level item non-clickable and enabled for desktop only.
            'id' => '7',
            'label' => $phTermLabel,
            'path' => '#rcs.category.url_path#',
            'clickable' => "0",
            'display_in_desktop' => "1",
            'display_in_mobile' => "0",
            'depth' => 2,
            'move_to_right' => TRUE,
            'highlight_paragraph' => [],
            'child' => [],
          ],
        ],
      ],
    ];

    // Set default parent_id 0 to load first level category terms.
    $parent_id = 0;
    $context = ['block' => $this->getBaseId()];

    $desktop_main_menu_layout = $this->configFactory->get('alshaya_main_menu.settings')->get('desktop_main_menu_layout');
    if ($desktop_main_menu_layout == 'default' || $desktop_main_menu_layout == 'menu_dynamic_display') {
      $columns_tree = $this->getColumnDataMenuAlgo($term_data);
      $this->moduleHandler->alter('alshaya_main_menu_links', $columns_tree, $parent_id, $context);
    }
    else {
      $this->moduleHandler->alter('alshaya_main_menu_links', $term_data, $parent_id, $context);
    }

    $desktop_main_menu_highlight_timing = (int) $this->configFactory->get('alshaya_main_menu.settings')->get('desktop_main_menu_highlight_timing');

    return [
      '#theme' => 'alshaya_main_menu_level1',
      '#settings' => [
        'desktop_main_menu_highlight_timing' => $desktop_main_menu_highlight_timing,
      ],
      '#term_tree' => $term_data,
      '#column_tree' => $columns_tree ?? [],
      '#menu_type' => $desktop_main_menu_layout,
      '#attributes' => [
        'class' => [
          'block-alshaya-main-menu',
        ],
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

}
