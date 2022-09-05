<?php

/**
 * @file
 * Contains Alo yoga related translations.
 */

/**
 * Alshaya alo yoga translation.
 */
function _alshaya_ay_transac_react_translations() {
  // @todo Add arabic translation for quick add strings.
  return [
    'v1' => [
      'view options' => [
        'en' => 'Quick Add',
      ],
      'add to cart' => [
        'en' => 'Add to Bag',
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
function _alshaya_ay_transac_get_translations(string $version = '') {
  $translations = _alshaya_ay_transac_react_translations();
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
