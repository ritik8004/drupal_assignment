<?php

namespace Drupal\alshaya_vs_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SizeFacetBlock' block.
 *
 * @Block(
 *  id = "size_facet_block",
 *  admin_label = @Translation("Size Facet Block"),
 * )
 */
class SizeFacetBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['band_cup'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['sfb-band-cup'],
      ],
    ];

    $build['band_cup']['title'] = [
      '#type' => 'markup',
      '#markup' => $this->t('BRAS BY BAND AND CUP'),
    ];

    $build['band_cup']['facets'] = [
      '#type' => 'markup',
      '#markup' => '<div class="sfb-facets-container"></div>',
    ];

    $build['letter'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['sfb-letter'],
      ],
    ];

    $build['letter']['title'] = [
      '#type' => 'markup',
      '#markup' => $this->t('BRAS BY LETTER'),
    ];

    $build['letter']['facets'] = [
      '#type' => 'markup',
      '#markup' => '<div class="sfb-facets-container"></div>',
    ];

    $size_guide = _alshaya_acm_product_get_size_guide_info(NULL);
    if (isset($size_guide['link'])) {
      $build['size_guide_link'] = [
        '#markup' => $size_guide['link'],
      ];
    }


    $build['#attached']['library'][] = 'alshaya_vs_search/size_facet_copy';

    return $build;
  }

}
