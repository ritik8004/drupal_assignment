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

    $sizeGroups = [];
    foreach ($items as $item) {
      if (isset($item['#title'], $item['#title']['#value'])) {
        if (strpos($item['#title']['#value'], ':') !== FALSE) {
          $sizeGroupArr = explode(':', $item['#title']['#value']);
          $item['#title']['#value'] = $sizeGroupArr[1];
          $sizeGroups[$sizeGroupArr[0]][] = $item;
        }
      }
    }

    $items = [];
    $othersLabel = (string) $this->t('other');
    // Moving others at the bottom of the list.
    if (isset($sizeGroups[$othersLabel])) {
      $copyOtherlabels = $sizeGroups[$othersLabel];
      unset($sizeGroups[$othersLabel]);
      $sizeGroups[(string) $this->t('other')] = $copyOtherlabels;
    }

    foreach ($sizeGroups ?? [] as $group => $sizes) {
      $groupItems = [];
      foreach ($sizes as $index => $size) {
        $size['#wrapper_attributes']['class'][] = 'sizegroup-child';
        $size['#wrapper_attributes']['class'][] = $group . '-child';
        $groupItems[$index]['element'] = $size;
        // Set active classe at wrapper(li) for childs.
        $groupItems[$index]['class'] = '';
        if ($size['#title']['#is_active']) {
          $groupItems[$index]['class'] = 'is-active';
        }
      }

      $items[] = [
        '#items' => $groupItems,
        '#value' => $group,
        '#theme' => 'facets_result_item_with_size_group',
      ];
    }

    $build['#items'] = $items;
    $build['#attributes']['class'][] = 'js-facets-checkbox-links';
    $build['#attached']['library'][] = 'facets/drupal.facets.checkbox-widget';
    $build['#attached']['library'][] = 'alshaya_product_options/sizegroup_filter';

    return $build;
  }

}
