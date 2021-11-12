<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a decoupled navigation menu block.
 *
 * @Block(
 *   id = "rcs_ph_navigation_menu",
 *   admin_label = @Translation("RCS Placeholders navigation menu block"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhNavigationMenu extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div id="rcs-ph-navigation_menu" data-rcs-dependency="none"><span></span></div>',
    ];
  }

}
