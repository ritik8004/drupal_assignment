<?php

/**
 * @file
 * File to create bazaar voice content for Mothercare.
 */

/**
 * Bazaar Voice review form configurations for Mothercare.
 */
function alshaya_mc_bazaar_voice_content(): array {
  $content = [];

  // Configuration in english language.
  $content['en'] = "rating:
  '#type': textfield
  '#required': true
  '#title': 'Overall Rating'
  '#value': null
  '#minlength': null
  '#id': rating
  '#maxlength': null
  '#default_value': null
  '#group_type': ratings
  '#visible': false
title:
  '#type': textfield
  '#required': true
  '#title': 'Review Title'
  '#value': null
  '#minlength': 0
  '#id': title
  '#maxlength': 50
  '#default_value': null
  '#group_type': textfield
  '#visible': true
reviewtext:
  '#type': textarea
  '#required': true
  '#title': Review
  '#value': null
  '#minlength': 50
  '#id': reviewtext
  '#maxlength': 1000
  '#default_value': null
  '#group_type': textarea
  '#visible': true
isrecommended:
  '#type': checkbox
  '#required': false
  '#title': 'Would you recommend this product to a friend?'
  '#value': null
  '#id': isrecommended
  '#default_value': null
  '#group_type': boolean
  '#visible': true
photo_upload:
  '#type': processed_text
  '#group_type': photo
  '#visible': false
  '#wrapper_attributes':
    class:
      - photo-upload
  '#text': |
    Upload photo
  '#format': full_text
usernickname:
  '#type': textfield
  '#required': true
  '#title': Nickname
  '#value': null
  '#minlength': 4
  '#id': usernickname
  '#maxlength': 25
  '#default_value': null
  '#group_type': textfield
  '#visible': true
  '#text': 'User Basic Info'
useremail:
  '#type': textfield
  '#required': true
  '#title': Email
  '#value': null
  '#minlength': 0
  '#id': useremail
  '#maxlength': 255
  '#default_value': null
  '#group_type': textfield
  '#visible': true
contextdatavalue_age_filter:
  '#type': select
  '#title': 'How old are you?'
  '#required': false
  '#default_value': ''
  '#options':
    1818: 'Under 18'
    1824: 18-24
    2534: 25-34
    3544: 35-44
    4554: 45-54
    5565: 55-65
    6565: 'Over 65'
  '#id': contextdatavalue_age_filter
  '#group_type': select
  '#visible': true
  '#text': 'Tell us more about yourself'
contextdatavalue_guardian_filter:
  '#type': select
  '#title': 'Are you a parent or Gifter?'
  '#required': false
  '#default_value': ''
  '#options':
    IAmDad: 'I am Dad'
    Mom: Mom
    LadyGifter: 'Lady Gifter'
    MaleGifter: 'Male Gifter'
    Grandpa: Grandpa
    Grandma: Grandma
  '#id': contextdatavalue_guardian_filter
  '#group_type': select
  '#visible': true
contextdatavalue_location_filter:
  '#type': select
  '#title': Locations
  '#required': false
  '#default_value': ''
  '#options':
    KW: Kuwait
    AE: 'United Arab Emirates'
    SA: 'Saudi Arabia'
    EG: Egypt
    BH: Bahrain
    QA: Qatar
    JO: Jordan
    LB: Lebanon
    MA: Morocco
    OM: Oman
  '#id': contextdatavalue_location_filter
  '#group_type': select
  '#visible': false
rating_quality:
  '#type': textfield
  '#required': false
  '#title': 'How would you rate the quality of this product?'
  '#value': null
  '#minlength': null
  '#id': rating_Quality
  '#maxlength': null
  '#default_value': null
  '#group_type': ratings
  '#visible': true
  '#text': Rating
hostedauthentication_rememberme:
  '#type': checkbox
  '#required': false
  '#title': null
  '#value': 'false'
  '#id': hostedauthentication_rememberme
  '#default_value': null
  '#group_type': boolean
  '#visible': false
sendemailalertwhenpublished:
  '#type': checkbox
  '#required': false
  '#title': null
  '#value': 'false'
  '#id': sendemailalertwhenpublished
  '#default_value': true
  '#group_type': boolean
  '#visible': false
sendemailalertwhencommented:
  '#type': checkbox
  '#required': false
  '#title': null
  '#value': 'false'
  '#id': sendemailalertwhencommented
  '#default_value': true
  '#group_type': boolean
  '#visible': false";

  // Configuration in arabic language.
  $content['ar'] = "rating:
  '#title': 'التقييم الإجمالي'
  '#default_value': null
title:
  '#title': 'عنوان التقييم'
  '#default_value': null
reviewtext:
  '#title': 'تقييم المنتج'
  '#default_value': null
isrecommended:
  '#title': 'هل تنصح بإستخدام هذا المنتج؟'
  '#default_value': null
photo_upload:
  '#text': |
    حمل الصورة
usernickname:
  '#title': 'اسم المستخدم'
  '#default_value': null
  '#text': 'معلومات المستخدم الأساسية.'
useremail:
  '#title': 'البريد الإلكتروني'
  '#default_value': null
contextdatavalue_age_filter:
  '#title': 'كم عمرك؟'
  '#default_value': ''
  '#options':
    1818: 'أقل من 18'
    1824: '24 - 18'
    2534: '34 - 25'
    3544: '44 - 35'
    4554: '54 - 45'
    5565: '65 - 55'
    6565: 'أكبر من 65'
  '#text': 'اخبرنا المزيد عنك'
contextdatavalue_guardian_filter:
  '#title': 'هل أنت أحد الوالدين أم ترغب في تقديم هدية؟'
  '#default_value': ''
  '#options':
    IAmDad: الأب
    Mom: الأم
    LadyGifter: 'سيدة ترغب في تقديم هدية'
    MaleGifter: 'سيد يرغب في تقديم هدية'
    Grandpa: الجد
    Grandma: الجدة
contextdatavalue_location_filter:
  '#title': 'الموقع'
  '#default_value': ''
  '#options':
    KW: 'الكويت'
    AE: 'الإمارات العربية المتحدة'
    SA: 'المملكة العربية السعودية'
    EG: 'مصر'
    BH: 'البحرين'
    QA: 'قطر'
    JO: 'الأردن'
    LB: 'لبنان'
    MA: 'المغرب'
    OM: 'عُمان'
rating_quality:
  '#title': 'كم تقييمك لجودة هذا المنتج؟'
  '#default_value': null
  '#text': تقييم
hostedauthentication_rememberme:
  '#title': null
  '#default_value': null
sendemailalertwhenpublished:
  '#title': null
  '#default_value': null
sendemailalertwhencommented:
  '#title': null
  '#default_value': null";

  return $content;
}
