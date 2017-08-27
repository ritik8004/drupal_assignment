<?php

namespace Drupal\alshaya_stores_migrate\Plugin\migrate\source;

use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\migrate\Row;

/**
 * Source for store.
 *
 * @MigrateSource(
 *   id = "store"
 * )
 */
class Store extends CSV {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Prepare the phone number.
    $phone_prefix = \Drupal::config('alshaya_stores_migrate.settings')->get('phone_prefix');
    $phone = $row->getSourceProperty('Primary phone');
    $row->setSourceProperty('phone', $phone_prefix . ' ' . $phone);

    // Prepare the opening hours.
    $days = [
      'Sunday',
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday',
    ];
    $map = [];
    foreach ($days as $day) {
      $map[] = [
        'day' => $day,
        'hours' => $row->getSourceProperty(ucfirst($day) . ' hours'),
      ];
    }
    $row->setSourceProperty('opening_hours', $map);

    return parent::prepareRow($row);
  }

}
