<?php

namespace Drupal\alshaya_product_options\Plugin\facets\widget;

use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\LinksWidget;

/**
 * The Size group list widget.
 *
 * @FacetsWidget(
 *   id = "multi_level_widget",
 *   label = @Translation("Multi Level Group of two attributes eg: Bra Size (Band Size,Cup Size)"),
 *   description = @Translation("Widget that shows bra size by group."),
 * )
 */
class MultiLevelWidget extends LinksWidget {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'granularity' => 20,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);
    return $build;
  }

}
