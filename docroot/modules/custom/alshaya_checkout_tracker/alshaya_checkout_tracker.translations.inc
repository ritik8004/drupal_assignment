<?php

/**
 * @file
 * Contains tracker related translations.
 */

/**
 * Tracker translation.
 */
function _alshaya_checkout_tracker_translations() {
  $strings = [
    'v1' => [
      'Bag' => [
        'ar' => 'حقيبة التسوق',
        'context' => 'alshaya_checkout_tracker',
      ],
      'Sign in' => [
        'ar' => 'تسجيل الدخول',
        'context' => 'alshaya_checkout_tracker',
      ],
      'Delivery and Payment' => [
        'ar' => 'الدفع والتوصيل',
        'context' => 'alshaya_checkout_tracker',
      ],
    ],
    'v2' => [
      'Confirmation' => [
        'ar' => 'التأكيد',
        'context' => 'alshaya_checkout_tracker',
      ],
    ],
  ];

  return $strings;
}

/**
 * Get translations for given version.
 *
 * @param string $version
 *   Version number like 'v1', 'v2' etc.
 *
 * @return array
 *   Translations.
 */
function _alshaya_checkout_tracker_get_translations(string $version = '') {
  $translations = _alshaya_checkout_tracker_translations();
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
