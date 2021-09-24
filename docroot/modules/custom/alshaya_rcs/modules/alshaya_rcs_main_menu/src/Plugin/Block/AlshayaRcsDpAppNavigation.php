<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides alshaya rcs dp app navigation block.
 *
 * @Block(
 *   id = "alshaya_rcs_dp_app_navigation",
 *   admin_label = @Translation("Alshaya Rcs Dp App Navigation")
 * )
 */
class AlshayaRcsDpAppNavigation extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return [
      '#theme' => 'alshaya_rcs_dp_app_navigation',
      '#data' => [
        'l2' => [
          'path' => '#rcs.app_nav.path#',
          'name' => '#rcs.app_nav.name#',
        ],
        'l3' => [
          'path' => '#rcs.app_nav.path#',
          'name' => '#rcs.app_nav.name#',
        ],
      ],
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-app_navigation',
            'data-param-entity-to-get' => 'navigation_menu',
          ],
        ],
      ],
    ];
  }

}
