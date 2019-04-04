<?php

namespace Drupal\alshaya_search_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a custom block which grid buttons and facet count.
 *
 * @Block(
 *  id = "alshaya_grid_count_block",
 *  admin_label = @Translation("Alshaya Grid/Count block"),
 * )
 */
class AlshayaGridCountBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'grid_count_block',
    ];
  }

}
