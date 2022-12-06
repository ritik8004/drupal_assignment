<?php

/**
 * @file
 * Contains Alo yoga related translations.
 */

/**
 * Alshaya alo yoga translation.
 */
function _alshaya_ay_transac_translations() {
  // @todo Add arabic translation for quick add strings.
  return [
    'v1' => [
      'view options' => [
        'en' => 'Quick Add',
        'ar' => 'إضافة سريعة',
      ],
      'add to cart' => [
        'en' => 'Add to Bag',
        'ar' => 'إضافة إلى حقيبة التسوق',
      ],
      'just dropped' => [
        'ar' => 'وصل للتو',
      ],
      'Length' => [
        'ar' => 'الطول',
      ],
      'Support' => [
        'ar' => 'الدعم',
      ],
      'Waist' => [
        'ar' => 'محيط الخصر',
      ],
      'List of all The Alo Yoga' => [
        'ar' => 'قائمة جميع منتجات ألو يوغا',
      ],
      'View All' => [
        'en' => 'Shop All',
        'ar' => 'تسوق جميع المنتجات',
      ],
      'Shop by Activity' => [
        'ar' => 'تسوق بحسب النشاط',
      ],
      'FIT' => [
        'ar' => 'القصّة',
      ],
      'FABRICATION' => [
        'ar' => 'الصُنع',
      ],
      'DESCRIPTION' => [
        'ar' => 'وصف المنتج',
      ],
      'HOW TO USE' => [
        'ar' => 'كيفيةالاستخدام',
      ],
      'WHY WE LOVE IT' => [
        'ar' => 'لماذا نعشقها',
      ],
      'Core' => [
        'ar' => 'كور',
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
  $translations = _alshaya_ay_transac_translations();
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
