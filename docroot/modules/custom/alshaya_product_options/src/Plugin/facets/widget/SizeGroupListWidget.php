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
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);
    $items = $build['#items'];

    $othersLabel = (string) $this->t('Others');
    $sizeGroups = [];
    foreach ($items as $item) {
      if (isset($item['#title'], $item['#title']['#value'])) {
        if (strpos($item['#title']['#value'], ':') !== FALSE) {
          $sizeGroupArr = explode(':', $item['#title']['#value']);
          $item['#title']['#value'] = $sizeGroupArr[1];
          $sizeGroups[$sizeGroupArr[0]][] = $item;
        }
        else {
          $sizeGroups[$othersLabel][] = $item;
        }
      }
    }

    $items = [];
    // Moving others at the bottom of the list.
    if (isset($sizeGroups[$othersLabel])) {
      $copyOtherlabels = $sizeGroups[$othersLabel];
      unset($sizeGroups[$othersLabel]);
      $sizeGroups[$othersLabel] = $copyOtherlabels;
    }

    foreach ($sizeGroups ?? [] as $group => $sizes) {
      $items[] = [
        '#value' => $group,
        '#theme' => 'facets_result_item_with_size_group',
        '#wrapper_attributes' => [
          'class' => ['sizegroup'],
          'id' => [$group],
        ],
      ];

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
