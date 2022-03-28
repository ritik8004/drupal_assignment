<?php

/**
 * @file
 * Contains Online Returns related translations.
 */

/**
 * Online Returns translation.
 */
function _alshaya_online_returns_translations() {
  $strings = [
    'v1' => [
      '@type orders can only be returned at stores' => [
        'ar' => '@type orders can only be returned at stores AR',
      ],
      'Not eligible for Return' => [
        'ar' => 'Not eligible for Return AR',
      ],
      'Return Items Online' => [
        'ar' => 'Return Items Online AR',
      ],
      'Search for a nearby store' => [
        'ar' => 'Search for a nearby store AR',
      ],
      'Or return directly at any one of our stores' => [
        'ar' => 'Or return directly at any one of our stores AR',
      ],
      'Find Stores' => [
        'ar' => 'Find Stores AR',
      ],
      'Select items to return' => [
        'ar' => 'Select items to return AR',
      ],
      'Item Code: @sku' => [
        'ar' => 'Item Code: @sku AR',
      ],
      'Quantity: @qty' => [
        'ar' => 'Quantity: @qty AR',
      ],
      'Unit Price' => [
        'ar' => 'Unit Price AR',
      ],
      'Order ID' => [
        'ar' => 'Order ID AR',
      ],
      'Total:' => [
        'ar' => 'Total: AR',
      ],
      'items' => [
        'ar' => 'items AR',
      ],
      'item' => [
        'ar' => 'item AR',
      ],
      'Order Total' => [
        'ar' => 'Order Total AR',
      ],
      'Return window closed on @date' => [
        'ar' => 'Return window closed on @date AR',
      ],
      'You have untill @date to return the items' => [
        'ar' => 'You have untill @date to return the items AR',
      ],
    ],
  ];

  return $strings;
}

/**
 * Get translations for given version.
 *
 * @param string $version
 *   Version number like 'v1' etc.
 *
 * @return array
 *   Translations.
 */
function _alshaya_online_returns_get_translations(string $version = '') {
  $translations = _alshaya_online_returns_translations();
  // If no version mentioned, return all version strings.
  if (empty($version)) {
    $trans_data = [];
    foreach ($translations as $trans) {
      $trans_data = array_merge($trans_data, $trans);
    }

    return $trans_data;
  }

  return $translations[$version] ?? [];
}
