<?php

namespace Drupal\alshaya_product_options\Plugin\facets\widget;

use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\LinksWidget;

/**
 * The Size group list widget.
 *
 * @FacetsWidget(
 *   id = "size_group_list",
 *   label = @Translation("List of size groups"),
 *   description = @Translation("Widget that shows size by group."),
 * )
 */
class SizeGroupListWidget extends LinksWidget {

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
    $items = $build['#items'];

    foreach ($items as $index => $item) {
      if (isset($item['#title'], $item['#title']['#value'])) {
        if (strpos($item['#title']['#value'], '||') !== FALSE) {
          $sizeGroupArr = explode('||', $item['#title']['#value']);
          $group = explode('|', $sizeGroupArr[0]);
          $size = explode('|', $sizeGroupArr[1]);
          $sizeGroups[$group[1]][$index] = $size[1];
          $item['#title']['#value'] = $size[1];
        }

        $items[$index] = $item;
      }
    }
    $build['#items'] = $sizeGroups;
    $build['#items'] = $items;
    $build['#attributes']['class'][] = 'js-facets-checkbox-links';
    $build['#attached']['library'][] = 'facets/drupal.facets.checkbox-widget';

    return $build;
  }

}
