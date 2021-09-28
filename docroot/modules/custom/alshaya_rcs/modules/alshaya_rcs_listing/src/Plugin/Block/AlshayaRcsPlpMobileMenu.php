<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a PLP mobile menu block.
 *
 * @Block(
 *   id = "alshaya_rcs_plp_mobile_menu",
 *   admin_label = @Translation("Alshaya RCS PLP Mobile Menu"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class AlshayaRcsPlpMobileMenu extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return [
      '#theme' => 'alshaya_rcs_plp_mobile_menu',
      '#data' => [
        'name' => '#rcs.plp_mobile_menu.name#',
        'path' => '#rcs.plp_mobile_menu.url_path#',
        'classes' => '#rcs.plp_mobile_menu.class#',
      ],
      '#attributes' => [
        'class' => [
          'block-views-blockproduct-category-level-3-block-2',
        ],
      ],
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-plp_mobile_menu',
            'data-param-entity-to-get' => 'category',
          ],
        ],
      ],
    ];
  }

}
