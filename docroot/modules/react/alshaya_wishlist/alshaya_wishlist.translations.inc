<?php

/**
 * @file
 * Contains wishlist related translations.
 */

/**
 * Wishlist feature translation.
 */
function _alshaya_wishlist_translations() {
  return [
    'v1' => [
      'Items are saved for this visit only. To save them for later, sign in to your account or register a new one.' => [
        'ar' => 'تم حفظ المنتجات لهذه الزيارة فقط. لحفظهم وتسوقهم لاحقاً، قم بتسجيل الدخول إلى حسابك أو سجّل حساب جديد.',
      ],
      'Add to @wishlist_label' => [
        'ar' => 'أضف إلى @wishlist_label',
        'context' => 'wishlist',
      ],
      'Added to @wishlist_label' => [
        'ar' => 'تم الإضافة إلى @wishlist_label',
        'context' => 'wishlist',
      ],
      'Remove' => [
        'ar' => 'قم بالإزالة',
        'context' => 'wishlist',
      ],
      'Move to @wishlist_label' => [
        'ar' => 'انتقل إلى @wishlist_label',
        'context' => 'wishlist',
      ],
      'Remove from @wishlist_label' => [
        'ar' => 'قم بالإزالة من @wishlist_label',
        'context' => 'wishlist',
      ],
      'my @wishlist_label' => [
        'ar' => 'الخاصة بي @wishlist_label',
        'context' => 'wishlist',
      ],
      'saved to your @wishlist_label on this visit' => [
        'ar' => 'تم الحفظ في قائمتك @wishlist_label في هذه الزيارة',
        'context' => 'wishlist',
      ],
      'saved to your @wishlist_label' => [
        'ar' => 'تم الحفظ في قائمتك @wishlist_label',
        'context' => 'wishlist',
      ],
      'Keep it for next time?' => [
        'ar' => 'ترغب في الاحتفاظ بها للزيارة القادمة؟',
        'context' => 'wishlist',
      ],
      'Sign in to your account or register a new one.' => [
        'ar' => 'سجّل الدخول إلى حسابك أو سجّل حساب جديد.',
        'context' => 'wishlist',
      ],
      'Sign in' => [
        'ar' => 'تسجيل الدخول',
        'context' => 'wishlist',
      ],
      'Register' => [
        'ar' => 'سجل الآن',
        'context' => 'wishlist',
      ],
      'Do you want to move this item to @wishlist_label?' => [
        'ar' => 'هل ترغب في نقل هذا المنتج إلى @wishlist_label؟',
        'context' => 'wishlist',
      ],
      'Move to basket' => [
        'ar' => 'انقل إلى السلة',
        'context' => 'wishlist',
      ],
      'Share All' => [
        'ar' => 'شارك جميع',
        'context' => 'wishlist',
      ],
      'Share Your List' => [
        'ar' => 'شارك قائمتك',
        'context' => 'wishlist',
      ],
      'Share all your @wishlist_label with friends and family.' => [
        'ar' => 'شارك الجميع @wishlist_label مع الأصدقاء والعائلة.',
        'context' => 'wishlist',
      ],
      'Copy Link' => [
        'ar' => 'انسخ الرابط',
        'context' => 'wishlist',
      ],
      'Link Copied' => [
        'ar' => 'تم نسخ الرابط',
        'context' => 'wishlist',
      ],
      'Email' => [
        'ar' => 'البريد الإلكتروني',
        'context' => 'wishlist',
      ],
      'Your @wishlist_label is empty.' => [
        'ar' => '@wishlist_label الخاصة بك فارغة.',
        'context' => 'wishlist',
      ],
      'Yes, move to @wishlist_label' => [
        'ar' => 'نعم، أضف',
        'context' => 'wishlist',
      ],
      'No, remove it' => [
        'ar' => 'قم بإزالتها',
        'context' => 'wishlist',
      ],
      'go shopping' => [
        'en' => 'Go Shopping',
        'ar' => 'اذهب للتسوق',
        'context' => 'wishlist',
      ],
    ],
    'v2' => [
      'Some product in your @wishlist_label doesn\'t exist anymore!' => [
        'ar' => 'بعض المنتجات في قائمة @wishlist_label لم تعد متوفرة',
        'context' => 'wishlist',
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
function _alshaya_wishlist_get_translations(string $version = ''): array {
  $translations = _alshaya_wishlist_translations();
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
