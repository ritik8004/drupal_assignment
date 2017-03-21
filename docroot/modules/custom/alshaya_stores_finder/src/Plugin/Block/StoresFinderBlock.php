<?php

namespace Drupal\alshaya_stores_finder\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;

/**
 * Provides stores finder block.
 *
 * @Block(
 *   id = "alshaya_stores_finder",
 *   admin_label = @Translation("Alshaya stores finder")
 * )
 */
class StoresFinderBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => Link::createFromRoute($this->t('Find Store'), 'view.stores_finder.page_1', [], [
        'attributes' =>
          [
            'class' => 'stores-finder',
          ],
      ])->toString(),
    ];
  }

}
