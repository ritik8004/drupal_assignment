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
      '#theme' => 'alshaya_rcs_promotional_banner',
      '#fields' => [
        'promotion_banner' => '#rcs.category_promo.promotion_banner#',
        'promotion_banner_mobile' => '#rcs.category_promo.promotion_banner_mobile#',
        'class' => '#rcs.category_promo.classes#',
      ],
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-promotional_banner',
            'data-param-entity-to-get' => 'category',
          ],
        ],
      ],
    ];
  }

}
