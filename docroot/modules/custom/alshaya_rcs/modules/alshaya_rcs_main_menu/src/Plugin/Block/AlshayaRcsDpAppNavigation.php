<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;

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
    $data = [];

    $node = _alshaya_advanced_page_get_department_node();
    // If department page, only then process further.
    if ($node instanceof NodeInterface) {
      $data = [
        'item' => [
          '#rcs.app_nav.url_path#' => '#rcs.app_nav.name#',
        ],
      ];
    }

    return [
      '#theme' => 'alshaya_rcs_dp_app_navigation',
      '#data' => $data,
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
