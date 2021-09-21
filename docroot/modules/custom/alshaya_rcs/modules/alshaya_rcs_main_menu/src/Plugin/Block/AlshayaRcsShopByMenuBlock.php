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
 *   admin_label = @Translation("Shop by")
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
    // Prepare a static term array with placeholders
    // for all the possible combinations.
    $term_data = [
      '1' => [
        // 1st Level item with clickable and enabled for both mobile an desktop.
        'id' => '1',
        'label' => '#rcs.shopbymenuItem.name#',
        'path' => '#rcs.shopbymenuItem.url_path#',
        'depth' => 1,
      ],
    ];

    // Get the alshaya rcs main menu config object.
    $alshaya_rcs_main_menu_settings = $this->configFactory->get('alshaya_rcs_main_menu.settings');

    // Return render array with all block elements.
    return [
      '#theme' => 'alshaya_shop_by',
      '#term_tree' => $term_data,
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-shop_by_menu',
            'data-rcs-dependency' => 'none',
          ],
        ],
      ],
      '#cache' => [
        'tags' => $alshaya_rcs_main_menu_settings->getCacheTags(),
      ],
    ];
  }

}
