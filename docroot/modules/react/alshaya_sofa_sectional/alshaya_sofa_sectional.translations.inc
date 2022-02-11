<?php

/**
 * @file
 * Contains sofa sectional related translations.
 */

/**
 * Sofa sectional translation.
 */
function _alshaya_sofa_sectional_translations() {
  $strings = [
    'v1' => [
      'your selection summary' => [
        'ar' => 'خياراتك',
      ],
      'Select options 1 to @length' => [
        'ar' => '@length حدد الخيارات 1 إلى',
      ],
      'Clear Options' => [
        'ar' => 'مسح الخيارات',
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
function _alshaya_sofa_sectional_get_translations(string $version = '') {
  $translations = _alshaya_sofa_sectional_translations();
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
