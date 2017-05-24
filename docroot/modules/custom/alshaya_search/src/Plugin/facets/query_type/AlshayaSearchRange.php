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
    $granularity = $this->getGranularity();
    if ($value < $granularity) {
      $startValue = 0;
      $endValue = $granularity;
    }
    else {
      $startValue = (($value - $granularity) + 1);
      $endValue = (($value + $granularity) - 1);
    }
    return [
      'start' => $startValue,
      'stop' => $endValue,
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
      $modulus = $value % $granularity;
      $divisor = (($value - $modulus) / $granularity);
      if ($modulus == 0) {
        $displayValue = ($granularity * ($divisor - 1) + 1) . ' - ' . ($granularity * $divisor);
      }
      else {
        $displayValue = (($granularity * $divisor) + 1) . ' - ' . ($granularity * ($divisor + 1));
      }
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
