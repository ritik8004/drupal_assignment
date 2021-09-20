<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a decoupled shop by menu block.
 *
 * @Block(
 *   id = "rcs_ph_shop_by_menu",
 *   admin_label = @Translation("RCS Placeholders shop by menu block"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhShopByMenu extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div id="rcs-ph-shop_by_menu" data-rcs-dependency="none"><span></span></div>',
    ];
  }

}
