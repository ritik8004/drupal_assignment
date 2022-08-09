<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a rcs promotion banner for PLP.
 *
 * @Block(
 *   id = "rcs_promotional_banner",
 *   admin_label = @Translation("Alshaya RCS Promotional Banner"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class AlshayaRcsPromotionalBanner extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return [
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-promotional_banner',
            'data-param-entity-to-get' => 'category',
          ],
        ],
      ],
      '#attached' => [
        'library' => [
          'alshaya_rcs_listing/promotional_banner',
        ],
      ],
    ];
  }

}
