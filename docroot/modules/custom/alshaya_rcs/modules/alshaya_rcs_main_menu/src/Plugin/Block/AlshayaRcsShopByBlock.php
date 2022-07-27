<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\Block;

use Drupal\alshaya_acm_product_category\Plugin\Block\AlshayaShopByBlock;

/**
 * Provides alshaya rcs shop by block.
 *
 * @Block(
 *   id = "alshaya_rcs_shop_by_block",
 *   admin_label = @Translation("Alshaya rcs shop by")
 * )
 */
class AlshayaRcsShopByBlock extends AlshayaShopByBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Return rcs shop by container.
    return [
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-shop_by_block',
            'data-rcs-dependency' => 'navigation_menu',
            'data-param-entity-to-get' => 'navigation_menu',
            'data-param-category_id' => $this->configFactory->get('alshaya_rcs_main_menu.settings')->get('root_category'),
          ],
        ],
      ],
      '#attached' => [
        'library' => [
          'alshaya_white_label/rcs-shop-by-block',
          'alshaya_rcs_main_menu/shop_by_menu',
        ],
      ]
    ];
  }

}
