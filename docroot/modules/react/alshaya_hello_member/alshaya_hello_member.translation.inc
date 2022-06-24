<?php

/**
 * @file
 * Contains Hello Member related translations.
 */

/**
 * Hello Member translation.
 */
function _alshaya_hello_member_react_translations() {
  return [
    'v1' => [
      'Congrats!' => [
        'ar' => 'تهاني!',
        'context' => 'hello_member',
      ],
      'Become a Member' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Become a member — don’t miss out on deals, offers, discounts and bonus vouchers.' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Add More & Get More' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Did you know that if you add some more information below you will earn more points and get a more personalised experience? How great is that?' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Date of birth' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'H&M wants to give you special treat on your birthday' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Gender' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      '- Select Gender -' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Male' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Female' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Prefer not to say' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'My Details' => [
        'ar' => 'معلومات العضوية',
        'context' => 'hello_member',
      ],
      'Points' => [
        'ar' => 'نقاط',
        'context' => 'hello_member',
      ],
      'Hi' => [
        'ar' => 'أهلاً',
        'context' => 'hello_member',
      ],
      'SHOW LESS' => [
        'ar' => 'تظهر أقل',
        'context' => 'hello_member',
      ],
      'SHOW ALL' => [
        'ar' => 'عرض الكل',
        'context' => 'hello_member',
      ],
      'Benefits' => [
        'ar' => 'فوائد',
        'context' => 'hello_member',
      ],
      'Your memership will be renewed on @expiry_date and points will be reset every year.' => [
        'ar' => 'سيتم تجديد عضويتك بنسبة @expiry_date وستتم إعادة تعيين النقاط كل عام.',
      ],
      '@points pt' => [
        'ar' => 'نقطة @points',
        'context' => 'hello_member',
      ],
      'Points earned' => [
        'ar' => 'النقاط التي أحرزتها',
        'context' => 'hello_member',
      ],
      'Purchase' => [
        'ar' => 'عملية الشراء',
        'context' => 'hello_member',
      ],
      'Submit Rating and Review' => [
        'ar' => 'إرسال التقييم والمراجعة',
        'context' => 'hello_member',
      ],
      'Complete your profile' => [
        'ar' => 'أكمل ملفك الشخصي',
        'context' => 'hello_member',
      ],
      'Please enter the OTP sent to' => [
        'ar' => 'أكمل ملفك الشخصي',
        'context' => 'hello_member',
      ],
      'Not recieved the code?' => [
        'ar' => 'أكمل ملفك الشخصي',
        'context' => 'hello_member',
      ],
      'Resend Code' => [
        'ar' => 'أكمل ملفك الشخصي',
        'context' => 'hello_member',
      ],
      'Verify' => [
        'ar' => 'أكمل ملفك الشخصي',
        'context' => 'hello_member',
      ],
      'Send OTP' => [
        'ar' => 'أكمل ملفك الشخصي',
        'context' => 'hello_member',
      ],
      'Please enter valid OTP' => [
        'ar' => 'أكمل ملفك الشخصي',
        'context' => 'hello_member',
      ],
      'Something went wrong please try again later' => [
        'ar' => 'أكمل ملفك الشخصي',
        'context' => 'hello_member',
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
function _alshaya_hello_member_get_translations(string $version = '') {
  $translations = _alshaya_hello_member_react_translations();
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
