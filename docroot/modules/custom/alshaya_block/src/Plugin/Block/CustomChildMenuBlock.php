<?php

namespace Drupal\alshaya_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Menu\MenuLinkTree;
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
   * The language manger.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

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
   *   The menu link tree service.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, MenuLinkTree $menu_tree, LanguageManager $language_manager) {
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
  public function defaultConfiguration() {
    return [
      'menu_type' => 'main',
      'parent_menu' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['child_item'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Display child item of menu'),
      '#description' => $this->t('Choose which child menu items you want to show.'),
    ];

    // @todo: Add a select list to select the menu type.
    $form['child_item']['menu_item'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'type-dependent-set'],
    ];

    // @todo: Add ajax call with select menu type to get the menu type from
    // select value.
    if (!empty($this->configuration['menu_type'])) {
      $menu_type = $this->configuration['menu_type'];
    }

    $options = $this->getMenuItems($menu_type);

    $form['child_item']['menu_item']['parent_menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu to use for logo'),
      '#default_value' => $this->configuration['parent_menu'],
      '#options' => $options,
      '#description' => $this->t('Select the parent menu item to display child.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $child_item = $form_state->getValue('child_item');
    $this->configuration['parent_menu'] = $child_item['menu_item']['parent_menu'];
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
    $menu_tree = \Drupal::menuTree();

    // Build the typical default set of menu tree parameters.
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth(1);
    $parameters->setMaxDepth(1);

    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
    $options = [];
    if (!empty($tree)) {
      foreach ($tree as $element) {
        $options[$element->link->getPluginId()] = $element->link->getTitle();
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $menu_name = $this->configuration['menu_type'];
    $parentID = $this->configuration['parent_menu'];

    // Build the typical default set of menu tree parameters.
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setRoot($parentID);
    $parameters->expandedParents = [];
    $parameters->setMinDepth(1);
    $parameters->setMaxDepth(1);
    // Load the tree based on this set of parameters.
    $tree = $this->menuTree->load(NULL, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    if (!empty($tree)) {
      $build['menu_items'] = $this->menuTree->build($tree);
      $build['menu_items']['#attributes']['class'][] = 'navigation__sub-menu';
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['config:system.menu.' . $this->configuration['menu_type']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:' . $this->configuration['menu_type']]);
  }

}
