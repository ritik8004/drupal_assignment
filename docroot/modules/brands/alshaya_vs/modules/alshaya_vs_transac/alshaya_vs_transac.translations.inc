<?php

/**
 * @file
 * Contains Victoria Secret related translations.
 */

/**
 * VS translation.
 */
function _alshaya_vs_transac_translations() {
  return [
    'v1' => [
      'Corporate Info' => [
        'ar' => ' تعرفي إلينا ',
      ],
      'Customer care' => [
        'ar' => ' خدمة الزبائن ',
      ],
      'Connect with us' => [
        'ar' => ' تواصلي معنا ',
      ],
      'Store Includes' => [
        'ar' => 'المحل يشمل',
      ],
      'I would like to receive exclusive deals from Victoria\'s Secret' => [
        'ar' => 'أرغب في استلام عروض حصرية من فيكتورياز سيكريت',
      ],
      'seo_brand_name' => [
        'ar' => 'فيكتوريا سيكريت',
      ],
      'Fragrance' => [
        'ar' => 'العطر',
      ],
      'Bra Size' => [
        'ar' => 'مقاس حمالة الصدر',
      ],
      'Product Category' => [
        'ar' => 'نوع المنتج',
      ],
      'Lining Level' => [
        'ar' => 'مستوى البطانة',
      ],
      'Style' => [
        'ar' => 'التصميم',
      ],
      'Bra Coverage' => [
        'ar' => '',
      ],
      'Structure' => [
        'ar' => 'الدعم',
      ],
      'Panty Waist Type' => [
        'ar' => 'خصر السروال',
      ],
    ],
    'v2' => [
      'Bra Size' => [
        'ar' => 'مقاس حمالة الصدر',
      ],
    ],
    'v3' => [
      'Product Category' => [
        'ar' => 'نوع المنتج',
      ],
      'Lining Level' => [
        'ar' => 'مستوى البطانة',
      ],
      'Style' => [
        'ar' => 'التصميم',
      ],
      'Bra Coverage' => [
        'ar' => '',
      ],
      'Structure' => [
        'ar' => 'الدعم',
      ],
      'Panty Waist Type' => [
        'ar' => 'خصر السروال',
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
function _alshaya_vs_transac_get_translations(string $version = '') {
  $translations = _alshaya_vs_transac_translations();
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
