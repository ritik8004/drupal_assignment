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
    $range = $this->getRange(floor($value));

    $t_options = [
      '@start' => alshaya_acm_price_format($range['start_display']),
      '@stop' => alshaya_acm_price_format($range['stop']),
    ];

    // If this is the first range, display, "under X".
    if (floor($range['start']) == 0) {
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

    if ($value % $granularity) {
      $start = $value - ($value % $granularity);
    }
    else {
      $start = $value;
    }

    $stop = $start + $granularity;

    // Add 0.001 or similar to ensure we don't have overlapping values.
    $start_raw = $start + 1 / pow(10, self::getDecimals());

    return [
      'start' => $start_raw,
      'start_display' => $start,
      'stop' => $stop,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // We display overlapping values in frontend, but internally
    // we need ranges that are exclusive of each other.
    // For instance on FE we show 4-6 and 6-8, here if there are products
    // with price exactly 6 it is shown in 4-6 by facets but for counting
    // we were displaying it in 6-8.
    if (!empty($this->results)) {
      // Get granularity to clean-up based on that.
      $granularity = $this->getGranularity();

      $filters = [];

      // Result contains quotes as it is stored as string.
      foreach ($this->results as $key => $result) {
        $filter = str_replace('"', '', $result['filter']);
        $filters[$filter] = $key;
      }

      // Sort them by key, we have int now.
      ksort($filters);

      foreach ($filters as $filter => $key) {
        // No checking for cases between 0 and granularity.
        if ($filter < $granularity) {
          continue;
        }

        // Check if the value is edge case (== granularity).
        if ($filter % $granularity === 0) {
          // We decrease it by 1 decimal point to ensure it is shown in 4 to 6
          // instead of 6 to 8 (by making it 5.999).
          $new_value = $filter - (1 / pow(10, self::getDecimals()));
          $this->results[$key]['filter'] = '"' . $new_value . '"';
        }
      }
    }

    // Call parent's build now to use the contrib code as is.
    return parent::build();
  }

  /**
   * Helper function to get decimal points to show.
   *
   * @return int
   *   Decimals point.
   */
  private static function getDecimals() {
    static $decimals;

    if (empty($decimals)) {
      $decimals = 3;

      if (\Drupal::moduleHandler()->moduleExists('acq_commerce')) {
        $config = \Drupal::configFactory()->get('acq_commerce.currency');
        $decimals = (int) $config->get('decimal_points');
      }
    }

    return $decimals;
  }

}
