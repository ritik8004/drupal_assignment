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

    $sizeGroups = [];
    foreach ($items as $item) {
      if (isset($item['#title'], $item['#title']['#value'])) {
        if (strpos($item['#title']['#value'], '||') !== FALSE) {
          $sizeGroupArr = explode('||', $item['#title']['#value']);
          $group = explode('|', $sizeGroupArr[0]);
          $size = explode('|', $sizeGroupArr[1]);
          $item['#title']['#value'] = $size[1];
          $sizeGroups[$group[1]][] = $item;
        }
        else {
          $sizeGroups[] = $item;
        }
      }
    }

    $items = [];
    foreach ($sizeGroups ?? [] as $group => $sizes) {
      // Check if no sizegroup.
      if (isset($sizes['#type'])) {
        $items[] = $sizes;
        continue;
      }
      else {
        // Create parent markup.
        $items[] = [
          '#value' => $group,
          '#theme' => 'facets_result_item_with_size_group',
          '#wrapper_attributes' => [
            'class' => [
              0 => 'sizegroup',
            ],
            'id' => [
              0 => $group,
            ],
          ],
        ];
      }

      foreach ($sizes as $size) {
        $size['#wrapper_attributes']['class'][] = 'sizegroup-child';
        $size['#wrapper_attributes']['class'][] = $group . '-child';
        $items[] = $size;
      }
    }

    $build['#items'] = $items;
    $build['#attributes']['class'][] = 'js-facets-checkbox-links';
    $build['#attached']['library'][] = 'facets/drupal.facets.checkbox-widget';
    $build['#attached']['library'][] = 'alshaya_product_options/sizegroup_filter';

    return $build;
  }

}
