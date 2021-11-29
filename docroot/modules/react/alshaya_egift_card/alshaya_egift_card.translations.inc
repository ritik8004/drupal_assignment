<?php

/**
 * @file
 * Contains eGift related translations.
 */

/**
 * eGift translation.
 */
function _alshaya_egift_card_translations() {
  $strings = [
    'v1' => [
      'Check Balance & Validity' => [
        'ar' => 'Check Balance & Validity',
        'context' => 'egift',
      ],
      'Enter gift card details to check balance and validity.' => [
        'ar' => 'Enter gift card details to check balance and validity.',
        'context' => 'egift',
      ],
      'eGift Card Number' => [
        'ar' => 'eGift Card Number',
        'context' => 'egift',
      ],
      'Something went wrong, please try again later.' => [
        'ar' => 'Something went wrong, please try again later.',
        'context' => 'egift',
      ],
      'Please enter your card number.' => [
        'ar' => 'Please enter your card number.',
        'context' => 'egift',
      ],
      'Terms & Conditions' => [
        'ar' => 'Terms & Conditions',
        'context' => 'egift',
      ],
      'Please enter your email address.' => [
        'ar' => 'Please enter your email address.',
        'context' => 'egift',
      ],
      'CHECK BALANCE' => [
        'ar' => 'CHECK BALANCE',
        'context' => 'egift',
      ],
      'Here is your card balance' => [
        'ar' => 'Here is your card balance',
        'context' => 'egift',
      ],
      'for eGift card ending' => [
        'ar' => 'for eGift card ending',
        'context' => 'egift',
      ],
      'Card Valid Up To' => [
        'ar' => 'Card Valid Up To',
        'context' => 'egift',
      ],
      'TOP UP CARD' => [
        'ar' => 'TOP UP CARD',
        'context' => 'egift',
      ],
      'CHECK ANOTHER CARD' => [
        'ar' => 'CHECK ANOTHER CARD',
        'context' => 'egift',
      ],
      'Style:' => [
        'ar' => 'Style:',
        'context' => 'egift',
      ],
      'Send to:' => [
        'ar' => 'Send to:',
        'context' => 'egift',
      ],
      'Message:' => [
        'ar' => 'Message:',
        'context' => 'egift',
      ],
      'Alshaya eGift card' => [
        'ar' => 'Alshaya eGift card',
        'context' => 'egift',
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
function _alshaya_egift_card_get_translations(string $version = '') {
  $translations = _alshaya_egift_card_translations();
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
