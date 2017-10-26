<?php

namespace Drupal\alshaya_search\Plugin\facets\query_type;

use Drupal\facets\Plugin\facets\query_type\SearchApiGranular;
use Drupal\facets\Result\Result;

/**
 * Basic support for numeric facets grouping by a granularity value.
 *
 * Requires the facet widget to set configuration value keyed with
 * granularity.
 *
 * @FacetsQueryType(
 *   id = "alshaya_search_granular",
 *   label = @Translation("Alshaya Search Range query with set granularity"),
 * )
 */
class AlshayaSearchGranular extends SearchApiGranular {

  /**
   * {@inheritdoc}
   */
  public function calculateRange($value) {
    return $this->getRange($value);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateResultFilter($value) {
    $range = $this->getRange($value);

    $t_options = [
      '@start' => alshaya_acm_price_format($range['start']),
      '@stop' => alshaya_acm_price_format($range['stop']),
    ];

    // If this is the first range, display, "under X".
    if ($range['start'] == 0) {
      $displayValue = t('under @stop', $t_options)->render();
    }
    else {
      $displayValue = t('@start - @stop', $t_options)->render();
    }

    return [
      'display' => $displayValue,
      'raw' => $range['start'],
    ];
  }

  /**
   * Provide a consistent way to create a start / stop range from a value.
   *
   * Ex: For a granularity of 10 and value of 7, range = 0-10.
   * Ex: For a granularity of 10 and value of 13, range = 11-20.
   * Ex: For a granularity of 10 and value of 30, range = 21-30.
   */
  private function getRange($value) {
    $granularity = $this->getGranularity();

    // Initial values.
    $start = 0;
    $stop = $granularity;

    if (fmod($value, $granularity) < 1) {
      $value = floor($value);
    }

    if ($value % $granularity) {
      $start = $value - ($value % $granularity);
    }
    else {
      $start = $value;
    }

    $stop = $start + $granularity;

    return [
      'start' => $start,
      'stop' => $stop,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $query_operator = $this->facet->getQueryOperator();

    // Go through the results and add facet results grouped by filters
    // defined by self::calculateResultFilter().
    if (!empty($this->results)) {
      $facet_results = [];
      foreach ($this->results as $key => $result) {
        if ($result['count'] || $query_operator == 'or') {
          $count = $result['count'];
          $result_filter = $this->calculateResultFilter(trim($result['filter'], '"'));
          if (isset($facet_results[$result_filter['raw']])) {
            $facet_results[$result_filter['raw']]->setCount(
              $facet_results[$result_filter['raw']]->getCount() + $count
            );
          }
          else {
            $facet_results[$result_filter['raw']] = new Result($result_filter['raw'], $result_filter['display'], $count);
          }
        }
      }

      $this->facet->setResults($facet_results);
    }
    return $this->facet;
  }

}
