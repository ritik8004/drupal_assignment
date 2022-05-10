<?php

namespace Drupal\alshaya_rcs_promotion\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides alshaya rcs promotion description block.
 *
 * @Block(
 *   id = "alshaya_rcs_promotion_description",
 *   admin_label = @Translation("Alshaya Rcs Promotion Description")
 * )
 */
class AlshayaRcsPromotionDescription extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'inside' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['c-page-title__description'],
        ],
        '#children' => '#rcs.promotion.description#',
      ],
    ];
  }

}
