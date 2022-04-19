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
      'orders can only be returned at stores' => [
        'ar' => 'يمكن إرجاع الطلبيات عبر المحلات فقط',
        'context' => 'online_returns',
      ],
      'Not eligible for Return' => [
        'ar' => 'غير قابل للإرجاع',
        'context' => 'online_returns',
      ],
      'Return Items Online' => [
        'ar' => 'إرجاع المنتجات عبر الموقع الإلكتروني',
        'context' => 'online_returns',
      ],
      'Search for a nearby store' => [
        'ar' => 'البحث عن أقرب المحلات إليك',
        'context' => 'online_returns',
      ],
      'Or return directly at any one of our stores' => [
        'ar' => 'من محلاتناّأو الإرجاع مباشرة عبر أي',
        'context' => 'online_returns',
      ],
      'Find Stores' => [
        'ar' => 'اكتشف أقرب محل',
        'context' => 'online_returns',
      ],
      '1. Select items to return' => [
        'ar' => '1. اختر المنتج المطلوب إرجاعه',
        'context' => 'online_returns',
      ],
      'Item Code: @sku' => [
        'ar' => 'كود المنتج: @sku',
        'context' => 'online_returns',
      ],
      'Quantity: @quantity' => [
        'ar' => 'الكمية: @quantity',
        'context' => 'online_returns',
      ],
      'Unit Price' => [
        'ar' => 'سعر الوحدة',
        'context' => 'online_returns',
      ],
      'Order ID' => [
        'ar' => 'الرقم التعريفي للطلبية',
        'context' => 'online_returns',
      ],
      'Total' => [
        'ar' => 'الإجمالي',
        'context' => 'online_returns',
      ],
      'items' => [
        'ar' => 'المنتجات',
        'context' => 'online_returns',
      ],
      'item' => [
        'ar' => 'المنتج',
        'context' => 'online_returns',
      ],
      'Order Total' => [
        'ar' => 'إجمالي الطلبية',
        'context' => 'online_returns',
      ],
      'Return window closed on @date' => [
        'ar' => 'سيتم إغلاق نافذة الإرجاع بتاريخ @date',
        'context' => 'online_returns',
      ],
      'You have untill @date to return the items' => [
        'ar' => 'الموعد النهائي لإرجاع المنتجات هو @date',
        'context' => 'online_returns',
      ],
      'Continue' => [
        'ar' => 'تابع',
        'context' => 'online_returns',
      ],
      'Reason for Return' => [
        'ar' => 'سبب الإرجاع',
        'context' => 'online_returns',
      ],
      'Select quantity' => [
        'ar' => 'اختر الكمية',
        'context' => 'online_returns',
      ],
      'Choose a reason' => [
        'ar' => 'اختر السبب',
        'context' => 'online_returns',
      ],
      "If you're trying to leave the Online Returns page, please note, any changes made will be lost." => [
        'ar' => 'يرجى العلم بأنك ستفقد جميع التغييرات في حال مغادرة صفحة الإرجاع عبر الموقع الإلكتروني',
        'context' => 'online_returns',
      ],
      '1 item' => [
        'ar' => '1 item AR',
        'context' => 'online_returns',
      ],
      '@count items' => [
        'ar' => '@count items AR',
        'context' => 'online_returns',
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
