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
      if (!empty($row->getSourceProperty(ucfirst($day) . ' hours'))) {
        $map[] = [
          'day' => $day,
          'hours' => $row->getSourceProperty(ucfirst($day) . ' hours'),
        ];
      }
    }
    $row->setSourceProperty('opening_hours', $map);

    // Prepare the address.
    $address = [];
    if ($line2 = $row->getSourceProperty('Address line 2')) {
      $address[] = $line2;
    }
    if ($locality = $row->getSourceProperty('Locality')) {
      $address[] = $locality;
    }
    if ($area = $row->getSourceProperty('Administrative area')) {
      $address[] = $area;
    }
    if ($country = $row->getSourceProperty('Country')) {
      $address[] = $country;
    }
    $address = '<p>' . implode('<br />', $address) . '</p>';

    if ($store_includes = $row->getSourceProperty('Store includes')) {
      $address .= '<br /><br /><p>' . $store_includes . '</p>';
    }
    $row->setSourceProperty('address', $address);

    return parent::prepareRow($row);
  }

}
