<?php

namespace Drupal\alshaya_custom_logo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Path\AliasStorage;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display 'Site branding' elements.
 *
 * @Block(
 *   id = "custom_logo_block",
 *   admin_label = @Translation("Custom Site Logo")
 * )
 */
class CustomLogoBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The language manger.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The alias storage.
   *
   * @var \Drupal\Core\Path\AliasStorage
   */
  protected $aliasStorage;

  /**
   * Creates a CustomLogoBlock instansce.
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
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Path\AliasStorage $alias_storage
   *   The alias storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, MenuLinkTree $menu_tree, CurrentPathStack $current_path, LanguageManager $language_manager, AliasStorage $alias_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->currentPath = $current_path;
    $this->languageManager = $language_manager;
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
      $container->get('path.current'),
      $container->get('language_manager'),
      $container->get('path.alias_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'use_site_logo' => TRUE,
      'use_menu_item_logo' => TRUE,
      'menu_option' => 'main',
      'logo_fallback' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Get the theme.
    $theme = $form_state->get('block_theme');

    // Get permissions.
    $url_system_theme_settings = new Url('system.theme_settings');
    $url_system_theme_settings_theme = new Url('system.theme_settings_theme', ['theme' => $theme]);

    if ($url_system_theme_settings->access() && $url_system_theme_settings_theme->access()) {
      // Provide links to the Appearance Settings and Theme Settings pages
      // if the user has access to administer themes.
      $site_logo_description = $this->t('Defined on the <a href=":appearance">Appearance Settings</a> or <a href=":theme">Theme Settings</a> page.', [
        ':appearance' => $url_system_theme_settings->toString(),
        ':theme' => $url_system_theme_settings_theme->toString(),
      ]);
    }
    else {
      // Explain that the user does not have access to the Appearance and Theme
      // Settings pages.
      $site_logo_description = $this->t('Defined on the Appearance or Theme Settings page. You do not have the appropriate permissions to change the site logo.');
    }

    $form['block_logo'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Toggle branding elements'),
      '#description' => $this->t('Choose which branding elements you want to show in this block instance.'),
    ];
    $form['block_logo']['use_site_logo'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Site logo'),
      '#description' => $site_logo_description,
      '#default_value' => $this->configuration['use_site_logo'],
    ];
    $form['block_logo']['use_menu_item_logo'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Menu item logo'),
      '#description' => $this->t('Check if you want to change site logo based on menu items.'),
      '#default_value' => $this->configuration['use_site_logo'],
    ];

    $form['block_logo']['menu_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu to use for logo'),
      '#default_value' => $this->configuration['menu_option'],
      '#options' => menu_ui_get_menus(),
      '#description' => $this->t('Select the menu to use to change the logo.'),
      '#states' => [
        'invisible' => [
          ':input[name="settings[block_logo][use_menu_item_logo]"]' => [
            'checked' => FALSE,
          ],
        ],
      ],
    ];

    $form['block_logo']['logo_fallback'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Logo fallback'),
      '#description' => $site_logo_description,
      '#default_value' => $this->configuration['use_site_logo'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $block_logo = $form_state->getValue('block_logo');
    $this->configuration['use_site_logo'] = $block_logo['use_site_logo'];
    $this->configuration['use_menu_item_logo'] = $block_logo['use_menu_item_logo'];
    $this->configuration['menu_option'] = $block_logo['menu_option'];
    $this->configuration['logo_fallback'] = $block_logo['logo_fallback'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $site_config = $this->configFactory->get('system.site');

    $build['site_logo'] = [
      '#theme' => 'image',
      '#uri' => theme_get_setting('logo.url'),
      '#alt' => $this->t('Home'),
      '#access' => $this->configuration['use_site_logo'],
    ];

    // If use menu item logo is true.
    if ($this->configuration['use_menu_item_logo']) {
      // Get the attributes of current menu.
      $current_logo = $this->getCurrentMenuAttributes();
      // Set the attributes to site_logo, that can be altered at theme level.
      $build['site_logo']['#attributes'] = ['data-logo-class' => $current_logo['class']];
      // Get the link for the logo.
      $build['target_link'] = [
        '#type' => 'link',
        '#title' => $current_logo['title'],
        '#url' => $current_logo['link'],
      ];
    }

    return $build;
  }

  /**
   * Get the current menu attributes based on current path.
   */
  protected function getCurrentMenuAttributes() {
    // Get current language code.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Get the main menu tree to get the current active path.
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters('main');
    $parameters->setTopLevelOnly();
    $tree = $this->menuTree->load($this->configuration['menu_option'], $parameters);

    // Retrieve an array which contains the path pieces.
    $current_path = $this->currentPath->getPath();
    // Get current path alias.
    $current_path_alias = $this->aliasStorage->load(['source' => $current_path, 'langcode' => $langcode]);

    // Get the active link if any!.
    foreach ($tree as $key => $element) {
      if ($element->inActiveTrail) {
        // @var $link \Drupal\Core\Menu\MenuLinkInterface
        $link = $element->link;
        $active_link = $link->getUrlObject()->toString();
        if (strpos($active_link, $current_path_alias['alias']) !== 0) {
          $attributes = menu_link_attributes_get_attributes($element->link);
          return [
            'class' => $attributes['class'],
            // @todo: Change this link to the parent link only,
            // We don't need the sublink for the link of the logo.
            'link' => $active_link,
            'title' => $link->getTitle(),
          ];
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['custom-logo']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
