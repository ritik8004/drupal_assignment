<?php
// @codingStandardsIgnoreFile

const DRUPAL_ROOT = __DIR__ . '/../../';

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';

$countries = [
  'kw' => 'Kuwait',
  'sa' => 'Saudi Arabia',
  'ae' => 'UAE',
];

$queues = [
  'hm' => [
    'brand' => 'H&M',
    'countries' => [
      'kw' => 98,
      'sa' => 99,
      'ae' => 100,
    ],
  ],
  'mc' => [
    'brand' => 'Mothercare',
    'countries' => [
      'kw' => 88,
      'sa' => 89,
      'ae' => 90,
    ],
  ],
  'bbw' => [
    'brand' => 'Bath and Bodyworks',
    'countries' => [
      'ae' => 87,
    ],
  ],
  'vs' => [
    'brand' => 'Victoria Secret',
    'countries' => [
      'ae' => 84,
    ]
  ],
];

foreach ($queues as $brand) {
  echo "=> " . $brand['brand'];
  foreach ($brand['countries'] as $country => $id) {
    $data = get_queue_total($id);
    echo "\n==> " . $countries[$country] . " : " . $data->total;
  }
  echo "\n\n";
}
