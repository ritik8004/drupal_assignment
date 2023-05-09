<?php

namespace Drupal\alshaya_product_options\Plugin\facets\widget;

use Drupal\facets\Plugin\facets\widget\LinksWidget;

/**
 * The attributes group list widget.
 *
 * For combining two attributes and showing as multilevel dropdown.
 * eg: Brasize(32 A) = combination of Bandsize(32) and Cupsize(A).
 *
 * @FacetsWidget(
 *   id = "multi_level_widget",
 *   label = @Translation("Multi Level Group of two attributes eg: Bra Size (Band Size,Cup Size)"),
 *   description = @Translation("Widget that shows bra size by group."),
 * )
 */
class MultiLevelWidget extends LinksWidget {

}
