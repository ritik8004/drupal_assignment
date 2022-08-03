<?php

namespace Drupal\alshaya_product_options\Plugin\facets\widget;

use Drupal\alshaya_acm_product\SkuManager;
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
    // If sizegroup setting is not enabled then make it default.
    if (!$this->isSizeGroupEnabled()) {
      $build['#attributes']['class'][] = 'js-facets-checkbox-links';
      $build['#attached']['library'][] = 'facets/drupal.facets.checkbox-widget';
      return $build;
    }
    $items = $build['#items'];

    $sizeGroups = [];
    foreach ($items as $item) {
      if (isset($item['#title'], $item['#title']['#value'])) {
        if (str_contains($item['#title']['#value'], SkuManager::SIZE_GROUP_SEPARATOR)) {
          $sizeGroupArr = explode(SkuManager::SIZE_GROUP_SEPARATOR, $item['#title']['#value']);
          $item['#title']['#value'] = $sizeGroupArr[1];
          $sizeGroups[$sizeGroupArr[0]][] = $item;
        }
      }
    }

    $items = [];
    $othersLabel = 'other';
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
    // Adding class for sizegroup filter.
    $build['#attributes']['class'][] = 'size_group_list';
    $build['#attached']['library'][] = 'facets/drupal.facets.checkbox-widget';
    $build['#attached']['library'][] = 'alshaya_product_options/sizegroup_filter';

    return $build;
  }

  /**
   * Check if size grouping filter is enabled.
   *
   * @return int
   *   0 if not available, 1 if size grouping available.
   */
  protected function isSizeGroupEnabled() {
    static $status = NULL;

    if (!isset($status)) {
      $status = \Drupal::config('alshaya_acm_product.settings')->get('enable_size_grouping_filter');
    }

    return $status;
  }

}
