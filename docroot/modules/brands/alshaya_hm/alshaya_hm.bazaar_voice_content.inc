<?php

/**
 * @file
 * File to create bazaar voice content for H&M.
 */

/**
 * Bazaar Voice review form configurations for HnM.
 */
function alshaya_hm_bazaar_voice_content(): array {
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
  '#minlength': 20
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
  '#class_name': user-nickname
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
  '#class_name': user-email
rating_truetosize:
  '#type': select
  '#title': 'True to Size'
  '#required': false
  '#default_value': null
  '#options':
    1: Small
    2: 'Spot on'
    3: Large
  '#id': rating_TrueToSize
  '#group_type': slider
  '#visible': true
  '#text': Product
rating_fit_1:
  '#type': select
  '#title': Fit
  '#required': false
  '#default_value': null
  '#options':
    1: Tight
    2: 'Spot on'
    3: Relaxed
  '#id': rating_Fit_1
  '#group_type': slider
  '#visible': true
rating_length_1:
  '#type': select
  '#title': Length
  '#required': false
  '#default_value': null
  '#options':
    1: Short
    2: 'Spot on'
    3: Long
  '#id': rating_Length_1
  '#group_type': slider
  '#visible': true
rating_width_1:
  '#type': select
  '#title': Width
  '#required': false
  '#default_value': null
  '#options':
    1: Narrow
    2: 'Spot on'
    3: Wide
  '#id': rating_Width_1
  '#group_type': slider
  '#visible': true
contextdatavalue_height_filter:
  '#type': select
  '#title': Height
  '#required': false
  '#default_value': ''
  '#options':
    1: 'Under 5''0\"'
    2: '5''0\"'
    3: '5''1\"'
    4: '5''2\"'
    5: '5''3\"'
    6: '5''4\"'
    7: '5''5\"'
    8: '5''6\"'
    9: '5''7\"'
    10: '5''8\"'
    11: '5''9\"'
    12: '5''10\"'
    13: '5''11\"'
    14: '6''0\"'
    15: '6''1\"'
    16: '6''2\"'
    17: '6''3\"'
    18: '6''4\"'
    19: '6''5\"'
    20: 'Over 6''5\"'
  '#id': contextdatavalue_height_filter
  '#group_type': select
  '#visible': true
  '#text': Personal
contextdatavalue_skin_type_filter:
  '#type': select
  '#title': 'Skin type'
  '#required': false
  '#default_value': ''
  '#options':
    NormalSkin: 'Normal skin'
    OilySkin: 'Oily skin'
    SensitiveSkin: 'Sensitive skin'
    CombinationSkin: 'Combination skin'
    DrySkin: 'Dry skin'
    MatureSkin: 'Mature skin'
  '#id': contextdatavalue_Skin_type_filter
  '#group_type': select
  '#visible': true
contextdatavalue_skintone_filter:
  '#type': select
  '#title': 'Skin tone'
  '#required': false
  '#default_value': ''
  '#options':
    DarkSkinTone: 'Dark skin tone'
    FairSkinTone: 'Fair skin tone'
    LightmediumSkinTone: 'Light-medium skin tone'
    MediumdarkSkinTone: 'Medium-dark skin tone'
  '#id': contextdatavalue_skintone_filter
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
  '#visible': true
sendemailalertwhencommented:
  '#type': checkbox
  '#required': false
  '#title': null
  '#value': 'false'
  '#id': sendemailalertwhencommented
  '#default_value': true
  '#group_type': boolean
  '#visible': false
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
hostedauthentication_authenticationemail:
  '#type': textfield
  '#required': false
  '#title': null
  '#value': null
  '#minlength': 0
  '#id': hostedauthentication_authenticationemail
  '#maxlength': 255
  '#default_value': null
  '#group_type': textfield
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
rating_truetosize:
  '#title': 'مقاس فعلي'
  '#default_value': null
  '#options':
    1: صغير
    2: مثالي
    3: كبير
  '#text': المنتج
rating_fit_1:
  '#title': القياس
  '#default_value': null
  '#options':
    1: مشدود
    2: مثالي
    3: مريح
rating_length_1:
  '#title': طول
  '#default_value': null
  '#options':
    1: قصير
    2: مثالي
    3: طويل
rating_width_1:
  '#title': عرض
  '#default_value': null
  '#options':
    1: ضيق
    2: مثالي
    3: واسع
contextdatavalue_height_filter:
  '#title': الطول
  '#default_value': ''
  '#options':
    1: 'أقل من 5''0\"'
    2: '0''5\"'
    3: '1''5\"'
    4: '2''5\"'
    5: '3''5\"'
    6: '4''5\"'
    7: '5''5\"'
    8: '6''5\"'
    9: '7''5\"'
    10: '8''5\"'
    11: '9''5\"'
    12: '10''5\"'
    13: '11''5\"'
    14: '0''6\"'
    15: '1''6\"'
    16: '2''6\"'
    17: '3''6\"'
    18: '4''6\"'
    19: '5''6\"'
    20: 'أكبر من 5''6\"'
  '#text': المستخدم
contextdatavalue_skin_type_filter:
  '#title': 'نوع البشرة'
  '#default_value': ''
  '#options':
    NormalSkin: 'بشرة عادية'
    OilySkin: 'بشرة دهنية'
    SensitiveSkin: 'بشرة حساسة'
    CombinationSkin: 'بشرة مختلطة'
    DrySkin: 'بشرة جافة'
    MatureSkin: 'بشرة متقدمة في السن'
contextdatavalue_skintone_filter:
  '#title': 'درجة لون البشرة'
  '#default_value': ''
  '#options':
    DarkSkinTone: 'درجة لون داكنة'
    FairSkinTone: 'درجة لون فاتحة'
    LightmediumSkinTone: 'درجة لون فاتحة-متوسطة'
    MediumdarkSkinTone: 'درجة لون متوسطة-داكنة'
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
sendemailalertwhencommented:
  '#title': null
  '#default_value': null
hostedauthentication_rememberme:
  '#title': null
  '#default_value': null
sendemailalertwhenpublished:
  '#title': null
  '#default_value': null
hostedauthentication_authenticationemail:
  '#title': null
  '#default_value': null";

  return $content;
}
