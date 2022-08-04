<?php

namespace Drupal\alshaya_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\alshaya_block\AlshayaBlockHelper;
use Drupal\path_alias\AliasRepositoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display 'Site branding' elements.
 *
 * @Block(
 *   id = "custom_child_menu_block",
 *   admin_label = @Translation("Custom Child Menu Block")
 * )
 */
class CustomChildMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Menu tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuTree;

  /**
   * The block helper class.
   *
   * @var \Drupal\alshaya_block\AlshayaBlockHelper
   */
  protected $alshayaBlockHelper;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The alias storage.
   *
   * @var \Drupal\path_alias\AliasRepositoryInterface
   */
  protected $aliasStorage;

  /**
   * Creates a CustomLogoBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Menu\MenuLinkTree $menu_tree
   *   The language manager.
   * @param \Drupal\alshaya_block\AlshayaBlockHelper $alshaya_block_helper
   *   The alias storage service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager service object.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Drupal\path_alias\AliasRepositoryInterface $alias_storage
   *   The alias storage service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              MenuLinkTree $menu_tree,
                              AlshayaBlockHelper $alshaya_block_helper,
                              LanguageManagerInterface $language_manager,
                              CurrentPathStack $current_path,
                              AliasRepositoryInterface $alias_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->alshayaBlockHelper = $alshaya_block_helper;
    $this->languageManager = $language_manager;
    $this->currentPath = $current_path;
    $this->aliasStorage = $alias_storage;
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
      $container->get('alshaya_block.helper'),
      $container->get('language_manager'),
      $container->get('path.current'),
      $container->get('path_alias.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'level' => 1,
      'depth' => 0,
      'menu_name' => 'main',
      'parent_menu_item' => '',
      'child_level' => 1,
      'child_depth' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $menu_type = NULL;
    $defaults = $this->defaultConfiguration();
    $form['menu_levels'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu levels'),
      // Open if not set to defaults.
      '#open' => $defaults['level'] !== $this->configuration['level'] || $defaults['depth'] !== $this->configuration['depth'],
      '#process' => [[self::class, 'processMenuLevelParents']],
    ];

    $options = range(0, $this->menuTree->maxDepth());
    unset($options[0]);
    $level_options = $options;

    $form['menu_levels']['level'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial visibility level'),
      '#default_value' => $this->configuration['level'],
      '#options' => $level_options,
      '#description' => $this->t('The menu is only visible if the menu item for the current page is at this level or below it. Use level 1 to always display this menu.'),
      '#required' => TRUE,
    ];

    $options[0] = $this->t('Unlimited');
    $depth_options = $options;

    $form['menu_levels']['depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of levels to display'),
      '#default_value' => $this->configuration['depth'],
      '#options' => $depth_options,
      '#description' => $this->t('This maximum number includes the initial level.'),
      '#required' => TRUE,
    ];

    $form['child_item'] = [
      '#type' => 'details',
      '#title' => $this->t('Display child items of default menu'),
      '#description' => $this->t('Choose which child menu items you want to show.'),
      '#open' => TRUE,
    ];

    // @todo Add a select list to select the menu type.
    $form['child_item']['menu_item'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'type-dependent-set'],
    ];

    // @todo Add ajax call with select menu type to get the menu type from
    // select value.
    if (!empty($this->configuration['menu_name'])) {
      $menu_type = $this->configuration['menu_name'];
    }

    $menu_options = $this->getMenuItems($menu_type);

    $form['child_item']['menu_item']['parent_menu_item'] = [
      '#type' => 'select',
      '#title' => $this->t('Default parent'),
      '#default_value' => $this->configuration['parent_menu_item'],
      '#options' => $menu_options,
      '#description' => $this->t('Select the default parent menu item to display child.'),
    ];

    $form['child_item']['menu_item']['child_level'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial visibility level'),
      '#default_value' => $this->configuration['child_level'],
      '#options' => $level_options,
      '#description' => $this->t('The menu is only visible if the menu item for the current page is at this level or below it. Use level 1 to always display this menu.'),
      '#required' => TRUE,
    ];

    $form['child_item']['menu_item']['child_depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of levels to display'),
      '#default_value' => $this->configuration['child_depth'],
      '#options' => $depth_options,
      '#description' => $this->t('This maximum number includes the initial level.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * Form API callback: Processes the menu_levels field element.
   *
   * Adjusts the #parents of menu_levels to save its children at the top level.
   */
  public static function processMenuLevelParents(&$element, FormStateInterface $form_state, &$complete_form) {
    array_pop($element['#parents']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['level'] = $form_state->getValue('level');
    $this->configuration['depth'] = $form_state->getValue('depth');
    $child_item = $form_state->getValue('child_item');
    $this->configuration['parent_menu_item'] = $child_item['menu_item']['parent_menu_item'];
    $this->configuration['child_level'] = $child_item['menu_item']['child_level'];
    $this->configuration['child_depth'] = $child_item['menu_item']['child_depth'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $tree = $this->getBuildItems();
    if (!empty($tree)) {
      $build['menu_items'] = $this->menuTree->build($tree);
      $build['menu_items']['#attributes']['class'][] = 'navigation__sub-menu';
    }
    return $build;
  }

  /**
   * Get menu items for the given menu name.
   *
   * @param string $menu_name
   *   The menu name.
   *
   * @return array
   *   Return the array of menu items for selected menu type.
   */
  protected function getMenuItems($menu_name = 'main') {
    // Build the typical default set of menu tree parameters.
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth(1);
    $parameters->setMaxDepth(1);

    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    $options = [];
    if (!empty($tree)) {
      foreach ($tree as $element) {
        $options[$element->link->getPluginId()] = $element->link->getTitle();
      }
    }
    return $options;
  }

  /**
   * Get the final build items based on the settings.
   *
   * @return array|\Drupal\Core\Menu\MenuLinkTreeElement[]|mixed
   *   Return array of menu links or null.
   */
  protected function getBuildItems($default = FALSE) {
    $menu_name = $this->configuration['menu_name'];
    $parent_menu_item = $this->configuration['parent_menu_item'];

    // Build the typical default set of menu tree parameters.
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    if ($default) {
      $level = $this->configuration['child_level'];
      $depth = $this->configuration['child_depth'];
      $parameters->setRoot($parent_menu_item);
      $parameters->expandedParents = [];
      // Load the tree based on set of parameters only, without any menu name.
      $menu_name = NULL;
    }
    else {
      $level = $this->configuration['level'];
      $depth = $this->configuration['depth'];
    }

    $parameters->setMinDepth($level);

    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $this->menuTree->maxDepth()));
    }

    $tree = $this->menuTree->load($menu_name, $parameters);

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    $tree = $this->menuTree->transform($tree, $manipulators);
    // Check if the current path belongs to the main menu or not.
    $main_menu_item = $this->alshayaBlockHelper->checkCurrentPathInMenu();
    // Build the default child menu items if current menu items doesn't have
    // any child and current path doesn't belongs to the menu item.
    if (empty($tree) && $default == FALSE && empty($main_menu_item)) {
      return $this->getBuildItems(TRUE);
    }

    return $tree;
  }

  /**
   * Get the current menu attributes based on current path.
   */
  protected function getCheckCurrentPathBelongsToMenu() {
    // Get current language code.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // @todo Make the menu name "main" dynamic.
    // Get the main menu tree to get the current active path.
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters('main');
    $parameters->setTopLevelOnly();
    $tree = $this->menuTree->load('main', $parameters);

    // Retrieve an array which contains the path pieces.
    $current_path = $this->currentPath->getPath();

    // Get current path alias.
    $current_path_alias = $this->aliasStorage->load([
      'source' => $current_path,
      'langcode' => $langcode,
    ]);

    // Get the active link if any!.
    foreach ($tree as $element) {
      if ($element->inActiveTrail) {
        /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
        $link = $element->link;
        $active_link = $link->getUrlObject()->toString();
        if (!str_starts_with($active_link, $current_path_alias['alias'])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['config:system.menu.' . $this->configuration['menu_name']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:' . $this->configuration['menu_name']]);
  }

}
