<?php

/**
 * @file
 * Contains new balance translations.
 */

/**
 * New Balance translation.
 */
function _alshaya_nb_transac_translations() {
  return [
    'v1' => [
      'seo_brand_name' => [
        'en' => 'New Balance',
        'ar' => 'نيو بالانس',
      ],
      'get email offers and the latest news from @site_name' => [
        'en' => 'Enjoy 10% off your first order',
        'ar' => 'استمتع بخصم 10٪ على طلبك الأول',
      ],
      'read more' => [
        'ar' => 'اقرأ المزيد',
      ],
      'read less' => [
        'ar' => 'اقرأ  أقل',
      ],
      'Product Description' => [
        'ar' => 'وصف المنتج',
      ],
      'Product No:' => [
        'ar' => 'رمز المنتج',
      ],
      'size guide' => [
        'ar' => 'دليل المقاسات',
      ],
    ],
    'v2' => [
      'Product Details' => [
        'ar' => 'تفاصيل المنتج',
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
function _alshaya_nb_transac_get_translations(string $version = '') {
  $translations = _alshaya_nb_transac_translations();
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
