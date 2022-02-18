<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides alshaya rcs super category menu block.
 *
 * @Block(
 *   id = "alshaya_rcs_super_category_menu",
 *   admin_label = @Translation("Alshaya Rcs super category menu")
 * )
 */
class AlshayaRcsSuperCategoryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request object.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * AlshayaSuperCategoryBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   Language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param Symfony\Component\HttpFoundation\RequestStack $request
   *   Entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ThemeManagerInterface $theme_manager,
    ConfigFactoryInterface $config_factory,
    RequestStack $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeManager = $theme_manager;
    $this->configFactory = $config_factory;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('theme.manager'),
      $container->get('config.factory'),
      $container->get('request_stack'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Don't need to build this block if status of super category settings
    // is false.
    if (!$this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      return [];
    }

    return [
      '#theme' => 'alshaya_super_category_top_level',
      '#term_tree' => [
        [
          'imgPath' => '#rcs.super_category.image#',
          'path' => '#rcs.super_category.url_path#',
          'meta_title' => '#rcs.super_category.meta_title#',
          'label' => '#rcs.super_category.name#',
          'inactive_path' => '#rcs.super_category.inactive_image#',
          'class' => '#rcs.super_category.classes#',
        ],
      ],
      '#attributes' => [
        'class' => [
          'block-alshaya-super-category-menu',
          'block-alshaya-super-category',
        ],
      ],
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-super_category',
            'data-param-entity-to-get' => 'navigation_menu',
            'data-param-category_id' => $this->configFactory->get('alshaya_rcs_main_menu.settings')->get('root_category'),
          ],
        ],
      ],
      '#attached' => [
        'library' => [
          'alshaya_rcs_main_menu/renderer',
          'alshaya_super_category/minimalistic_header',
        ],
        'drupalSettings' => [
          'superCategory' => [
            'search_facet' => AlshayaSuperCategoryManager::SEARCH_FACET_NAME,
          ],
          'theme' => [
            'path' => $this->themeManager->getActiveTheme()->getPath(),
          ],
        ],
      ],
    ];
  }

}
