<?php

/**
 * @file
 * Contains eGift related translations.
 */

/**
 * Geolocation translations.
 */
function _alshaya_geolocation_translations() {
  $strings = [
    'v1' => [
      'Collect from store in @time' => [
        'ar' => 'استلم من المحل بعد @time',
        'context' => 'click and collect',
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
function _alshaya_geolocation_get_translations(string $version = '') {
  $translations = _alshaya_geolocation_translations();
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
