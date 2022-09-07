<?php

/**
 * @file
 * Contains eGift related translations.
 */

/**
 * Alshaya Security translation.
 */
function _alshaya_security_translations() {
  $strings = [
    'v1' => [
      'You can\'t request reset password email more than @max_limit times in a day. You have exceeded your reset limit today. Please try again later.' => [
        'ar' => 'لا يمكنك طلب إعادة تعيين كلمة المرور عبر البريد الإلكتروني لأكثر من @max_limit مرة في اليوم. لقد تجاوزت حد إعادة التعيين اليوم. الرجاء معاودة المحاولة في وقت لاحق.',
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
function _alshaya_security_get_translations(string $version = '') {
  $translations = _alshaya_security_translations();
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
