<?php

namespace Drupal\alshaya_acm_product\Plugin\facets\widget;

use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\CheckboxWidget;

/**
 * The Delivery widget.
 *
 * @FacetsWidget(
 *   id = "delivery_ways",
 *   label = @Translation("Delivery Types"),
 *   description = @Translation("Widget for delivery."),
 * )
 */
class DeliveryTypeWidget extends CheckboxWidget {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);
    return $build;
  }

}
