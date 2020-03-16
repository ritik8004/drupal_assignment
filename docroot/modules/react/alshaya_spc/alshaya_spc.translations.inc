<?php

/**
 * @file
 * Contains SPC related translations.
 */

/**
 * SPC translation.
 */
function _alshaya_spc_translations() {
  return [
    'v1' => [
      'have a promo code?' => [
        'ar' => 'هل لديك رمز عرض ؟',
      ],
      'Promo code' => [
        'ar' => 'رمز عرض',
      ],
      'subtotal' => [
        'ar' => 'المبلغ',
      ],
      'excluding delivery' => [
        'ar' => 'قبل التوصيل',
      ],
      'Shopping Bag' => [
        'ar' => 'حقيبة التسوق',
      ],
      'continue to checkout' => [
        'ar' => 'إتمام الشراء بأمان',
      ],
      'my shopping bag (@qty items)' => [
        'ar' => '(قطعة @qty) حقيبة التسوق',
      ],
      'Total (excluding delivery)' => [
        'ar' => '(المبلغ الإجمالي (قبل التوصيل',
      ],
      'remove this item' => [
        'ar' => 'أزل هذا البند',
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
function _alshaya_spc_get_translations(string $version = '') {
  $translations = _alshaya_spc_translations();
  // If no version mentioned, return all version strings.
  if (empty($version)) {
    $trans_data = [];
    foreach ($translations as $ver => $trans) {
      $trans_data = array_merge($trans_data, $trans);
    }

    return $trans_data;
  }

  return $translations[$version] ?? [];
}
