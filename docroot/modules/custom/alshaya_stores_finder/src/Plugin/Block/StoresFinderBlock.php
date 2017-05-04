<?php

namespace Drupal\alshaya_stores_finder\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Cache\Cache;

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
    $is_active = '';
    // Current route name.
    $current_route = \Drupal::routeMatch()->getRouteName();
    // If current page, add class.
    if ($current_route == 'view.stores_finder.page_2') {
      $is_active = 'is-active';
    }

    return [
      '#markup' => Link::createFromRoute($this->t('Find Store'), 'view.stores_finder.page_2', [], [
        'attributes' =>
          [
            'class' => ['stores-finder', $is_active],
          ],
      ])->toString(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
