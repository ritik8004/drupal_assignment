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
      'Not eligible for Return' => [
        'ar' => 'Not eligible for Return AR',
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
