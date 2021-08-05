<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dynamic LHN Block for commerce pages.
 *
 * @Block(
 *   id = "rcs_ph_lhn",
 *   admin_label = @Translation("RCS Placeholders LHN"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhLhnBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config to enable/disable the lhn category tree.
   */
  const ENABLE_DISABLE_CONFIG = 'alshaya_acm_product_category.settings';

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaCategoryLhnBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'rcs-ph-lhn_block',
        'data-param-get-data' => 'false',
        'class' => ['block-alshaya-category-lhn-block'],
      ],
    ];

    $build['wrapper']['content'] = [
      '#theme' => 'alshaya_rcs_lhn_tree',
      '#lhn_cat_tree' => [
        // Clickable.
        [
          'lhn' => 1,
          'label' => '#rcs.lhn.name#',
          'url' => '#rcs.lhn.url_path#',
          'depth' => '#rcs.lhn.level#',
          'active' => '#rcs.lhn.active#',
          'clickable' => TRUE,
        ],
        // Unclickable.
        [
          'lhn' => 1,
          'label' => '#rcs.lhn.name#',
          'url' => '#rcs.lhn.url_path#',
          'depth' => '#rcs.lhn.level#',
          'active' => '#rcs.lhn.active#',
          'clickable' => FALSE,
        ],
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $config = $this->configFactory->get(self::ENABLE_DISABLE_CONFIG);
    // Not allow if lhn is disabled.
    return AccessResult::allowedif($config->get('enable_lhn_tree'));
  }

}
