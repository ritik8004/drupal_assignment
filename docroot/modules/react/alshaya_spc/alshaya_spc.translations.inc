<?php

/**
 * @file
 * Contains SPC related translations.
 */

/**
 * SPC translation.
 */
function _alshaya_spc_translations() {
  $strings = [
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
  ];

  alshaya_i18n_save_translations($strings);
}
