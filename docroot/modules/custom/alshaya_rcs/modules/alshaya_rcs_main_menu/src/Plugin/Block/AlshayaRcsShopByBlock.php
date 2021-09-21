<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alshaya rcs shop by block.
 *
 * @Block(
 *   id = "alshaya_rcs_shop_by_block",
 *   admin_label = @Translation("Alshaya rcs shop by")
 * )
 */
class AlshayaRcsShopByBlock extends BlockBase {

  /**
   * AlshayaMegaMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
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

    // Return render array with all block elements.
    return [
      '#theme' => 'alshaya_shop_by',
      '#term_tree' => $term_data,
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-shop_by_block',
            'data-param-entity-to-get' => 'navigation_menu',
          ],
        ],
      ],
    ];
  }

}
