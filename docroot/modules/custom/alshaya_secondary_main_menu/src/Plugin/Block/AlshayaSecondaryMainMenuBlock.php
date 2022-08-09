<?php

namespace Drupal\alshaya_secondary_main_menu\Plugin\Block;

use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Url;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides alshaya secondary main menu block.
 *
 * @Block(
 *   id = "alshaya_secondary_main_menu",
 *   admin_label = @Translation("Alshaya secondary main menu")
 * )
 */
class AlshayaSecondaryMainMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {
  public const MENU_NAME = 'secondary-main-menu';
  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;
  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, MenuLinkTreeInterface $menu_tree, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->languageManager = $language_manager;
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
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $desktop_config = $this->configFactory->get('alshaya_secondary_main_menu.settings');
    $desktop_secondary_main_menu_layout = $desktop_config->get('desktop_secondary_main_menu_layout');
    $desktop_secondary_main_menu_highlight_timing = (int) $desktop_config->get('desktop_secondary_main_menu_highlight_timing');
    $subtree = $this->getSubTree();
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
      '#settings' => [
        'desktopSecondaryMainMenuHighlightTiming' => $desktop_secondary_main_menu_highlight_timing,
      ],
      '#attributes' => [
        'class' => [
          'megamenu-dynamic-layout',
        ],
      ],
      '#attached' => [
        'library' => [
          'alshaya_secondary_main_menu/secondary_main_menu',
          'alshaya_white_label/secondary-menu',
        ],
      ],
      '#items' => $menu,
      '#column_tree' => $columns_tree,
      '#menu_type' => $desktop_secondary_main_menu_layout,
    ];
  }

  /**
   * Logic to get menu tree.
   */
  public function getSubTree() {
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters(self::MENU_NAME);
    $parameters->expandedParents = [];
    $parameters->setMinDepth(1);
    $tree = $this->menuTree->load(self::MENU_NAME, $parameters);
    return($tree);
  }

  /**
   * Column data after menu algo is applied.
   */
  public function getColumnDataMenuAlgo($menu) {
    $columns_tree = [];
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $default_lang = $this->languageManager->getDefaultLanguage()->getId();
    foreach ($menu['#items'] as $l2s) {
      if ($l2s['original_link'] instanceof MenuLinkContent) {
        $entity = $l2s['original_link']->getTranslateRoute()->getOption('entity');
        $l2s['highlight_paragraph'] = $entity->get('field_secondary_menu_highlight')->getValue();
        $l2s['gtm_menu_title'] = $entity->hasTranslation($default_lang) ? $entity->getTranslation($default_lang)->getTitle() : $l2s['original_link']->getTitle();
        foreach ($l2s['highlight_paragraph'] as $tr) {
          $paragraph = Paragraph::load($tr['target_id']);
          if ($language != $default_lang && !($paragraph->hasTranslation($language))) {
            $paragraph = $paragraph->getTranslation($default_lang);
          }
          $l2s['highlight_paragraph']['paragraph_type'] = $paragraph->getParagraphType()->id();
          $l2s['highlight_paragraph']['img'] = $paragraph->field_highlight_image->getValue();
          $l2s['highlight_paragraph']['imageUrl'] = $paragraph->get('field_highlight_image')->entity ? $paragraph->get('field_highlight_image')->entity->uri->value : NULL;
          $l2s['highlight_paragraph']['image_link'] = $paragraph->field_highlight_link->getValue();
          foreach ($l2s['highlight_paragraph']['image_link'] as $himg_link) {
            $l2s['highlight_paragraph']['image_link'] = Url::fromUri($himg_link['uri']);
          }
          $l2s['highlight_paragraph']['title'] = $paragraph->get('field_highlight_title')->value;
          $l2s['highlight_paragraph']['subtitle'] = $paragraph->get('field_highlight_subtitle')->value;
          $l2s['highlight_paragraph']['ishighlight'] = $entity->get('field_add_highlights');
        }
      }

      // Adding GTM menu title with English label to all children.
      foreach ($l2s['below'] as &$submenu) {
        $submenuEntity = $submenu['original_link']->getTranslateRoute()->getOption('entity');
        $submenu['gtm_menu_title'] = $submenuEntity->hasTranslation($default_lang) ? $submenuEntity->getTranslation($default_lang)->getTitle() : $submenu['original_link']->getTitle();
        foreach ($submenu['below'] as &$lowestChild) {
          $lowestChildEntity = $lowestChild['original_link']->getTranslateRoute()->getOption('entity');
          $lowestChild['gtm_menu_title'] = $lowestChildEntity->hasTranslation($default_lang) ? $lowestChildEntity->getTranslation($default_lang)->getTitle() : $lowestChild['original_link']->getTitle();
        }
      }

      $max_nb_col = (int) $this->configFactory->get('alshaya_secondary_main_menu.settings')->get('max_nb_col');
      $ideal_max_col_length = (int) $this->configFactory->get('alshaya_secondary_main_menu.settings')->get('ideal_max_col_length');
      $max_nb_col = $max_nb_col > 0 ? $max_nb_col : 6;
      $ideal_max_col_length = $ideal_max_col_length > 0 ? $ideal_max_col_length : 10;
      do {
        $columns = [];
        $col = 0;
        $col_total = 0;
        $reprocess = FALSE;
        // 2 below means L2 item + one blank line for spacing.
        $l2_cost = 2 + (is_countable($l2s['below']) ? count($l2s['below']) : 0);
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
        $columns = $l2s['below'];
        $col_total += $l2_cost;
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
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:secondary-main-menu']);
  }

}
