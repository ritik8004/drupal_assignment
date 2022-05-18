<?php

/**
 * @file
 * Contains Hello Member related translations.
 */

/**
 * Hello Member translation.
 */
function _alshaya_hello_member_react_translations() {
  return [
    'v1' => [
      'H&M Membership' => [
        'ar' => 'عضوية H&M',
        'context' => 'hello_member',
      ],
      'Membership info' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
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
function _alshaya_hello_member_get_translations(string $version = '') {
  $translations = _alshaya_hello_member_react_translations();
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
