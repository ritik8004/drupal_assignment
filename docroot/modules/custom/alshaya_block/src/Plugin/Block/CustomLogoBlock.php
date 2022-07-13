<?php

namespace Drupal\alshaya_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_block\AlshayaBlockHelper;
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
   * The block helper class.
   *
   * @var \Drupal\alshaya_block\AlshayaBlockHelper
   */
  protected $alshayaBlockHelper;

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
   * @param \Drupal\alshaya_block\AlshayaBlockHelper $alshaya_block_helper
   *   The alias storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, AlshayaBlockHelper $alshaya_block_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->alshayaBlockHelper = $alshaya_block_helper;
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
      $container->get('alshaya_block.helper')
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

    $form['block_logo'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Toggle branding elements'),
      '#description' => $this->t('Choose which branding elements you want to show in this block instance.'),
    ];
    $form['block_logo']['use_site_logo'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Site logo'),
      '#description' => $this->t('Use the logo of current theme.'),
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
      '#description' => $this->t("Use the default logo, if menu doesn't have specific logo exists."),
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

    $build['site_logo'] = [
      '#theme' => 'image',
      '#uri' => theme_get_setting('logo.url'),
      '#alt' => $this->t('Home'),
      '#access' => $this->configuration['use_site_logo'],
    ];

    // Default link to front page.
    $build['target_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Home'),
      '#url' => Url::fromRoute('<front>')->setAbsolute()->toString(),
    ];

    // If use menu item logo is true.
    if ($this->configuration['use_menu_item_logo']) {
      // Get the attributes of current menu.
      $attr = $this->getCurrentMenuAttributes();
      // Set the attributes to site_logo, that can be altered at theme level.
      $build['site_logo']['#attributes'] = ['data-logo-class' => $attr['class']];
      // Get the link for the logo.
      if (!empty($attr['title'])) {
        $build['target_link']['#title'] = $attr['title'];
      }

      if (!empty($attr['link'])) {
        $build['target_link']['#url'] = $attr['link'];
      }
    }

    return $build;
  }

  /**
   * Get the current menu attributes based on current path.
   */
  protected function getCurrentMenuAttributes() {
    // Get the current path attributes from the given menu.
    $helper = $this->alshayaBlockHelper->checkCurrentPathInMenu($this->configuration['menu_option']);
    if (!empty($helper)) {
      $element = $helper['element'];
      $options = $element->link->getOptions();

      $attributes = $options['attributes'] ?? [];

      // Class attribute needs special handling because Drupal
      // may add an "active" class to it.
      if (isset($attributes['class']) && !is_array($attributes['class'])) {
        $attributes['class'] = explode(' ', $attributes['class']);
      }

      // Remove empty attributes by checking their string length.
      foreach ($attributes as &$attribute) {
        if (!is_array($attribute) && mb_strlen($attribute) === 0) {
          unset($attribute);
        }
      }

      return [
        'class' => $attributes['class'],
        'link' => $helper['active_link'],
        'title' => $element->link->getTitle(),
      ];
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['config:system.menu.' . $this->configuration['menu_option']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:' . $this->configuration['menu_option']]);
  }

}
