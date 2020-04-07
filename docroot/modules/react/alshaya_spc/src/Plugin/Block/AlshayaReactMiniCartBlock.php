<?php

namespace Drupal\alshaya_spc\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'AlshayaReactMiniCartBlock' block.
 *
 * @Block(
 *   id = "alshaya_react_mini_cart",
 *   admin_label = @Translation("Alshaya React Cart Mini Cart Block"),
 * )
 */
class AlshayaReactMiniCartBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#type' => 'markup',
      '#markup' => '<div id="mini-cart-wrapper"></div><div id="cart_notification"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/mini_cart',
        ],
      ],
    ];

    return $build;
  }

}
