<?php

namespace Drupal\alshaya_bazaar_voice\Plugin\facets\widget;

use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\LinksWidget;

/**
 * The Star rating widget.
 *
 * @FacetsWidget(
 *   id = "star_rating",
 *   label = @Translation("Star rating"),
 *   description = @Translation("Widget that shows star ratings."),
 * )
 */
class StarRatingWidget extends LinksWidget {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);

    return $build;
  }

}
