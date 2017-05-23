<?php

namespace Drupal\alshaya_search\Plugin\facets\query_type;

use Drupal\facets\QueryType\QueryTypeRangeBase;

/**
 * Basic support for numeric facets grouping by a granularity value.
 *
 * Requires the facet widget to set configuration value keyed with
 * granularity.
 *
 * @FacetsQueryType(
 *   id = "alshaya_search_range",
 *   label = @Translation("Alshaya Search Range query with set granularity"),
 * )
 */
class AlshayaSearchRange extends QueryTypeRangeBase {

  /**
   * {@inheritdoc}
   */
  public function calculateRange($value) {
    return [
      'start' => ($value + 1),
      'stop' => ($value + $this->getGranularity()),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateResultFilter($value) {
    $granularity = $this->getGranularity();
    if ($value < $granularity) {
      $displayValue = $this->t('under @granularity', ['@granularity' => $granularity]);
    }
    else {
      $modulusValue = $value - ($value % $granularity);
      $displayValue = ($modulusValue + 1) . ' - ' . ($modulusValue + $granularity);
    }
    return [
      'display' => $displayValue,
      'raw' => $value - ($value % $this->getGranularity()) ,
    ];
  }

  /**
   * Looks at the configuration for this facet to determine the granularity.
   *
   * Default behaviour an integer for the steps that the facet works in.
   *
   * @return mixed
   *   If not an integer the inheriting class needs to deal with calculations.
   */
  protected function getGranularity() {
    return $this->facet->getWidgetInstance()->getConfiguration()['granularity'];
  }

}
