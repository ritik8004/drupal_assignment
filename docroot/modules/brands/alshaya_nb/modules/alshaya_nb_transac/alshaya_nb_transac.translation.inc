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
    'v3' => [
      'Independent since 1906, we empower people through sport and craftsmanship to create positive change in communities around the world.' => [
        'ar' => 'شركة مستقلة منذ عام 1906 لتمكين الجميع من خلال الرياضة والتصميم بحرفية، من أجل التغيير الإيجابي في المجتمعات حول العالم.',
      ],
      '© 2022 New Balance. All Rights Reserved' => [
        'ar' => '© 2022 نيو بالانس. جميع الحقوق محفوظة.',
      ],
      'Style' => [
        'ar' => 'الستايل',
      ],
      'Color' => [
        'ar' => 'اللون',
      ],
      'Gender' => [
        'ar' => 'الجنس',
      ],
      'Line' => [
        'ar' => 'التشكيلة',
      ],
      'Fit' => [
        'ar' => 'القصة',
      ],
      'Sustainability' => [
        'ar' => 'الاستدامة',
      ],
      'Width Description' => [
        'ar' => 'مواصفات العرض',
      ],
      'Product type' => [
        'ar' => 'نوع المنتج',
      ],
      'Closure type' => [
        'ar' => 'نوع إبزيم الإغلاق',
      ],
      'Green leaf' => [
        'ar' => 'غرين ليف',
      ],
      'Primary Material' => [
        'ar' => 'المواد الأساسية',
      ],
      'Technologies' => [
        'ar' => 'تقنيات التصميم',
      ],
      'Weight' => [
        'ar' => 'الوزن',
      ],
    ],
    'v4' => [
      'Meets the New Balance green leaf standard' => [
        'ar' => 'يلبي معايير غرين ليف من نيو بالانس',
      ],
    ],
    'v5' => [
      'Features' => [
        'ar' => 'المميزات',
      ],
    ],
    'v6' => [
      'Move to basket' => [
        'en' => 'Move to Cart',
        'ar' => 'انقل لعربة التسوق',
      ],
      'Bag' => [
        'en' => 'Cart',
        'ar' => 'حقيبة التسوق',
      ],
      'Basket' => [
        'en' => 'Cart',
        'ar' => 'حقيبة التسوق',
      ],
      'add to cart' => [
        'en' => 'Add to Cart',
        'ar' => 'أضف لعربة التسوق',
      ],
      'my shopping bag' => [
        'en' => 'my shopping cart',
        'ar' => 'عربة التسوق الخاصة بي',
      ],
      'Shopping Bag' => [
        'en' => 'Shopping Cart',
        'ar' => 'عربة التسوق',
      ],
      'view cart' => [
        'en' => 'view cart',
        'ar' => 'عرض عربة التسوق',
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
