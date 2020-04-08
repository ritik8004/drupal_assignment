<?php

namespace Drupal\alshaya_acm_promotion\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'AlshayaCartPromotionsBlock' block.
 *
 * @Block(
 *  id = "alshaya_cart_promotions_block",
 *  admin_label = @Translation("Alshaya cart promotions block"),
 * )
 */
class AlshayaCartPromotionsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function build() {
    // @TODO: Remove the block in future release.
    // We remove the config during install in alshaya_spc.
    return [];
  }

}
