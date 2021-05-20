<?php

namespace Drupal\alshaya_secondary_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;

/**
 * Provides alshaya secondary main menu block.
 *
 * @Block(
 *   id = "alshaya_secondary_main_menu",
 *   admin_label = @Translation("Alshaya secondary main menu")
 * )
 */
class AlshayaSeconadaryMainMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {
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
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * AlshayaSecondaryMenuBlock constructor.
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, MenuLinkTreeInterface $menu_tree, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
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
      $container->get('menu.link_tree'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $desktop_secondary_main_menu_layout = $this->configFactory->get('alshaya_secondary_main_menu.settings')->get('desktop_secondary_main_menu_layout');
    $menu_name = 'secondary-main-menu';
    $subtree = $this->getSubTree($menu_name);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($subtree, $manipulators);
    $menu = $this->menuTree->build($tree);
    // If no data, no need to render the block.
    if (empty($menu['#items'])) {
      return [];
    }
    $columns_tree = $this->getColumnDataMenuAlgo($menu);
    return [
      '#theme' => 'alshaya_secondary_main_menu_level1',
      '#items' => $menu,
      '#column_tree' => $columns_tree,
      '#menu_type' => $desktop_secondary_main_menu_layout,
    ];

  }

  /**
   * Logic to get menu tree.
   */
  public function getSubTree($menu_name) {
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->expandedParents = [];
    $active_trail = array_keys($parameters->activeTrail);
    $parent_link_id = isset($active_trail[1]) ? $active_trail[1] : $active_trail[0];
    // Having the parent now we set it as starting point to build our custom
    // tree.
    $parameters->setRoot($parent_link_id);
    $parameters->setMinDepth(1);
    $parameters->excludeRoot();
    $tree = $this->menuTree->load($menu_name, $parameters);
    return($tree);
  }

  /**
   * Column data after menu algo is applied.
   */
  public function getColumnDataMenuAlgo($menu) {
    $columns_tree = [];
    foreach ($menu['#items'] as $l2s) {
      $max_nb_col = (int) $this->configFactory->get('alshaya_secondary_main_menu.settings')->get('max_nb_col');
      $ideal_max_col_length = (int) $this->configFactory->get('alshaya_secondary_main_menu.settings')->get('ideal_max_col_length');
      $max_nb_col = $max_nb_col > 0 ? $max_nb_col : 6;
      $ideal_max_col_length = $ideal_max_col_length > 0 ? $ideal_max_col_length : 10;
      do {
        $columns = [];
        $col = 0;
        $col_total = 0;
        $reprocess = FALSE;
        foreach ($l2s['below'] as $l3s) {
          // 2 below means L2 item + one blank line for spacing).
          $l2_cost = 2 + count($l3s['below']);
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
      $columns_tree[$l2s['title']] = [
        'l1_object' => $l2s,
        'columns' => $columns,
      ];
    }
    return $columns_tree;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Even when the menu block renders to the empty string for a user, we want
    // the cache tag for this menu to be set: whenever the menu is changed, this
    // menu block must also be re-rendered for that user, because maybe a menu
    // link that is accessible for that user has been added.
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:system.menu.' . 'secondary-main-menu';
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // ::build() uses MenuLinkTreeInterface::getCurrentRouteMenuTreeParameters()
    // to generate menu tree parameters, and those take the active menu trail
    // into account. Therefore, we must vary the rendered menu by the active
    // trail of the rendered menu.
    // Additional cache contexts, e.g. those that determine link text or
    // accessibility of a menu, will be bubbled automatically.
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:' . 'secondary-main-menu']);
  }

}
