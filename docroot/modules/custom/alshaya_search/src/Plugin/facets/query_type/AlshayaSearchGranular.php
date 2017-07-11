<?php

namespace Drupal\alshaya_search\Plugin\facets\query_type;

use Drupal\facets\Plugin\facets\query_type\SearchApiGranular;

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
    $range = $this->getRange(ceil($value));

    // If this is the first range, display, "under X".
    if ($range['start'] === 0) {
      $displayValue = $this->t('under @stop', ['@stop' => $range['stop']]);
    }
    else {
      $displayValue = $range['start'] . ' - ' . $range['stop'];
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

    if ($value > $granularity) {
      // If we are at the end of a range, we need to start one range back.
      if (($value % $granularity) === 0) {
        $start = $value - $granularity + 1;
      }
      // Otherwise, we need to find the closest range by removing the remainder.
      else {
        // Remove the remainder.
        $start = $value - ($value % $granularity) + 1;
      }

      // Calculate the stop of the range.
      $stop = $start + $granularity - 1;
    }

    return [
      'start' => $start,
      'stop' => $stop,
    ];
  }

}
