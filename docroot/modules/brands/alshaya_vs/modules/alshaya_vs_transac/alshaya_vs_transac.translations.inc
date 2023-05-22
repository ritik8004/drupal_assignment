<?php

/**
 * @file
 * Contains Victoria Secret related translations.
 */

/**
 * VS translation.
 */
function _alshaya_vs_transac_translations() {
  return [
    'v1' => [
      'Bra Size' => [
        'ar' => 'مقاس حمالة الصدر',
      ],
    ],
  ];
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
function _alshaya_vs_transac_get_translations(string $version = '') {
  $translations = _alshaya_vs_transac_translations();
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
