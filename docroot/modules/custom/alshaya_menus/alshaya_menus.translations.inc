<?php

/**
 * @file
 * Contains Alshaya Menus translations.
 */

/**
 * Alshaya Menus translation.
 */
function _alshaya_menus_translations() {
  $strings = [
    'v1' => [
      'Select country' => [
        'ar' => 'حدد الدولة',
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
function _alshaya_menus_get_translations(string $version = NULL) {
  $translations = _alshaya_menus_translations();
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
