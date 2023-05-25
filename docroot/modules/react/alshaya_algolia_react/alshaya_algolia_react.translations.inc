<?php

/**
 * @file
 * Contains Algolia serch related translations.
 */

/**
 * Algolia serch translation.
 */
function _alshaya_algolia_react_translations() {
  $strings = [
    'v1' => [
      'top suggestions' => [
        'ar' => 'أفضل الإقتراحات',
      ],
      'showing @current of @total items' => [
        'ar' => 'عرض @current من إجمالي @total',
      ],
      'Load more products' => [
        'ar' => 'تحميل المزيد من المنتجات',
      ],
      'clear filters' => [
        'ar' => 'مسح الاختيارات',
      ],
      'Promotions' => [
        'ar' => 'العروض',
      ],
      'Shop all products in this offer' => [
        'ar' => 'تسوق جميع المنتجات المشمولة بهذا العرض',
      ],
      'Offers available' => [
        'ar' => 'العروض متوفرة',
      ],
      'You can combine promotions but only one offer code can be used per transaction' => [
        'ar' => 'يمكنك الجمع بين العروض الترويجية ولكن لا يمكن استخدام إلا رمز عرض واحد لكل معاملة تجارية',
      ],
    ],
    'v2' => [
      'Nothing found for' => [
        'ar' => 'وجدت شيئا ل',
      ],
      'no results found for' => [
        'ar' => 'لا يوجد نتائج ل',
      ],
      'showing results for' => [
        'ar' => 'عرض النتائج ل',
      ],
      'Unfortunately, nothing matches your search. Please try another search term, or browse by category below.' => [
        'ar' => 'لسوء الحظ ، لا شيء يطابق بحثك. يرجى محاولة مصطلح بحث آخر ، أو تصفح حسب الفئة أدناه.',
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
function _alshaya_algolia_react_get_translations(string $version = '') {
  $translations = _alshaya_algolia_react_translations();
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
