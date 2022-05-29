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
      'Congrats!' => [
        'ar' => 'تهاني!',
        'context' => 'hello_member',
      ],
      'Become a Member' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Become a member — don’t miss out on deals, offers, discounts and bonus vouchers.' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Add More & Get More' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Did you know that if you add some more information below you will earn more points and get a more personalised experience? How great is that?' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Date of birth' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'H&M wants to give you special treat on your birthday' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Gender' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      '- Select Gender -' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Male' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Female' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Prefer not to say' => [
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
