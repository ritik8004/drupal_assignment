<?php

/**
 * @file
 * Contains Tamara related translations.
 */

/**
 * Tamara translation.
 */
function _alshaya_tamara_translations() {
  $strings = [
    'v1' => [
      'Continue with tamara' => [
        'ar' => 'استمر مع تمارا',
        'context' => 'tamara',
      ],
      'Your tamara order has been cancelled' => [
        'ar' => 'تم إلغاء طلب تمارا',
        'context' => 'tamara',
      ],
    ],
    'v2' => [
      'Tamara' => [
        'ar' => 'تمارا',
        'context' => 'tamara',
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
function _alshaya_tamara_get_translations(string $version = '') {
  $translations = _alshaya_tamara_translations();
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
