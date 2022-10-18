<?php

/**
 * @file
 * Contains alo yoga translations.
 */

/**
 * Alo yoga translation.
 */
function _alshaya_ay_translations() {
  return [
    'v1' => [
      'seo_brand_name' => [
        'en' => 'Alo Yoga',
        'ar' => 'متجر Alo',
      ],
      'read more' => [
        'ar' => 'اقرأ المزيد',
      ],
      'read less' => [
        'ar' => 'اقرأ  أقل',
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
function _alshaya_ay_get_translations(string $version = '') {
  $translations = _alshaya_ay_translations();
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
