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
      'applied' => [
        'ar' => 'تم تفعيل',
      ],
      'complete purchase' => [
        'ar' => 'سجل الطلبية',
      ],
      'order summary (@qty items)' => [
        'ar' => '(@qty قطعة) ملخص الطلبية',
      ],
      'Order total (@count items)' => [
        'ar' => '(@count قطعة) المبلغ الإجمالي',
      ],
      'billing information' => [
        'ar' => 'يرجى تزويدنا ببيانات الاتصال الخاصة بكم والعنوان',
      ],
      'change address' => [
        'ar' => 'تغيير العنوان',
      ],
      'select' => [
        'ar' => 'تحديد',
      ],
      'selected' => [
        'ar' => 'المحدد',
      ],
      'Please add your contact details & address' => [
        'ar' => 'يرجى تزويدنا ببيانات الاتصال الخاصة بكم والعنوان',
      ],
      'deliver to my location' => [
        'ar' => 'التوصيل إلى موقعي',
      ],
      'contact information' => [
        'ar' => 'معلومات الاتصال',
      ],
      'select your preferred collection store' => [
        'ar' => 'حدد متجر المجموعة المفضل لديك',
      ],
      'please add your contact details and address.' => [
        'ar' => 'يرجى تزويدنا ببيانات الاتصال الخاصة بكم والعنوان',
      ],
      'select @label' => [
        'ar' => 'label@ تحديد',
      ],
      'Full Name' => [
        'ar' => 'الاسم الكامل',
      ],
      'find your nearest store' => [
        'ar' => 'ابحث عن المحلات القريبة إليك لاستلام طلبيتك',
      ],
      'CVV' => [
        'ar' => 'CVV الرقم السري',
      ],
      'Expiry' => [
        'ar' => 'تاريخ انتهاء البطاقة',
      ],
      'Your shopping bag is empty' => [
        'ar' => 'حقيبة التسوق الخاصة بك فارغة',
      ],
      'We will send you a text message once your order is ready for collection.' => [
        'ar' => 'سنقوم بإرسال رسالة قصيرة عندما تكون الطلبية جاهزة للاستلام',
      ],
      'We have set your billing address same as delivery address. You can select a different one by clicking the change button above.' => [
        'ar' => 'لقد قمنا بتعيين عنوان الفاتورة الخاص بك مثل عنوان التسليم. يمكنك تحديد واحد مختلف عن طريق النقر فوق زر التغيير أعلاه.',
      ],
      '@distance miles' => [
        'ar' => '@distance ميل',
      ],
      'Thanks for shopping with us.' => [
        'ar' => '.شكراً لتسوقكم معنا',
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
    foreach ($translations as $trans) {
      $trans_data = array_merge($trans_data, $trans);
    }

    return $trans_data;
  }

  return $translations[$version] ?? [];
}
