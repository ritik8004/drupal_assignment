<?php

namespace Drupal\alshaya_search_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block\BlockInterface;

/**
 * Provides a custom block which contains/renders all facet blocks for PLP.
 *
 * @Block(
 *  id = "alshaya_plp_facets_block_all",
 *  admin_label = @Translation("Alshaya all facet block - PLP"),
 * )
 */
class AlshayaPlpFacetsBlock extends BlockBase {

  /**
   * Facet source.
   */
  const FACET_SOURCE = 'search_api:views_block__alshaya_product_list__block_1';

  const PLP_EXPOSED_SORT_BLOCK = 'exposedformalshaya_product_listblock_1';

  /**
   * {@inheritdoc}
   */
  public function build() {
    /* @var \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager*/
    $facet_manager = \Drupal::service('facets.manager');
    // Get all facets of the given source.
    $facets = $facet_manager->getFacetsByFacetSourceId(self::FACET_SOURCE);
    $blocks = [];
    if (!empty($facets)) {
      foreach ($facets as $facet) {
        $block_id = str_replace('_', '', $facet->id());
        /* @var \Drupal\block\Entity\Block $block*/
        $block = \Drupal::entityTypeManager()->getStorage('block')->load($block_id);
        // If block is enabled.
        if ($block instanceof BlockInterface && $block->status()) {
          $blocks[] = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
        }
      }
    }

    $block = \Drupal::entityTypeManager()->getStorage('block')->load(self::PLP_EXPOSED_SORT_BLOCK);
    if ($block instanceof BlockInterface) {
      $block_view = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
      array_unshift($blocks, $block_view);
    }

    return [
      '#theme' => 'plp_all_facets_html',
      '#facet_blocks' => $blocks,
    ];
  }

}
