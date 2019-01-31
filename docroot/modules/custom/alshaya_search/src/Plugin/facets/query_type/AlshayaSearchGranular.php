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

        if ((float) $filter > 0) {
          $filters[$filter] = $key;
        }
        else {
          // Remove all the results with price exactly zero.
          // This for sure should be considered as corrupt data but for
          // configurable products that are OOS we will have price zero.
          // Still, we really don't want to show them when filtering by price,
          // do we?
          // Besides, views currently don't show it at all creating mismatch
          // in count in facets and count in results.
          unset($this->results[$key]);
        }
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
   * {@inheritdoc}
   */
  public function calculateResultFilter($value) {
    $range = $this->getRange(floor($value));

    $t_options = [
      '@start' => alshaya_acm_price_format($range['start']),
      '@stop' => alshaya_acm_price_format($range['stop']),
    ];

    // If this is the first range, display, "under X".
    if (floor($range['start']) == 0) {
      $displayValue = t('under @stop', $t_options)->render();
    }
    else {
      $displayValue = t('@start - @stop', $t_options)->render();
    }

    // Invoke the alter hook to allow all modules to update the price facet.
    \Drupal::moduleHandler()->alter('alshaya_search_facet_price', $range, $displayValue);

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

    return [
      'start' => $start,
      'stop' => $stop,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $query = $this->query;

    // Alter the query here.
    if (!empty($query)) {
      $options = &$query->getOptions();

      $operator = $this->facet->getQueryOperator();
      $field_identifier = $this->facet->getFieldIdentifier();
      $exclude = $this->facet->getExclude();
      $options['search_api_facets'][$field_identifier] = [
        'field' => $field_identifier,
        'limit' => $this->facet->getHardLimit(),
        'operator' => $this->facet->getQueryOperator(),
        'min_count' => $this->facet->getMinCount(),
        'missing' => FALSE,
      ];

      // Add the filter to the query if there are active values.
      $active_items = $this->facet->getActiveItems();
      $filter = $query->createConditionGroup($operator, ['facet:' . $field_identifier]);
      if (count($active_items)) {
        foreach ($active_items as $value) {
          $range = $this->calculateRange($value);

          $item_filter = $query->createConditionGroup('AND', ['facet:' . $field_identifier]);

          // Below line is the only change from default implementation of
          // parent::build(). We want to keep the results exclusive in
          // calculation but overlapping on display (frontend) so we use
          // GT instead GTEQ in condition below.
          $item_filter->addCondition($this->facet->getFieldIdentifier(), $range['start'], $exclude ? '<' : '>');
          $item_filter->addCondition($this->facet->getFieldIdentifier(), $range['stop'], $exclude ? '>' : '<=');

          $filter->addConditionGroup($item_filter);
        }
        $query->addConditionGroup($filter);
      }
    }
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
