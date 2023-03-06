<?php

/**
 * @file
 * File to create bazaar voice content for AEO.
 */

/**
 * Bazaar Voice review form configurations for AEO.
 */
function alshaya_aeo_transac_bazaar_voice_content(): array {
  $content = [];

  // Configuration in english language.
  $content['en'] = "rating:
  '#type': textfield
  '#required': true
  '#title': 'OVERALL RATING'
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
  '#title': 'REVIEW TITLE'
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
  '#title': REVIEW
  '#value': null
  '#minlength': 50
  '#id': reviewtext
  '#maxlength': 1000
  '#default_value': null
  '#group_type': textarea
  '#visible': true
photo_upload:
  '#type': processed_text
  '#group_type': photo
  '#visible': true
  '#wrapper_attributes':
    class:
      - photo-upload
  '#text': |
    UPLOAD PHOTO
  '#format': full_text
usernickname:
  '#type': textfield
  '#required': true
  '#title': NICKNAME
  '#value': null
  '#minlength': 4
  '#id': usernickname
  '#maxlength': 25
  '#default_value': null
  '#group_type': textfield
  '#visible': true
  '#text': 'USER BASIC INFO'
  '#class_name': user-nickname
useremail:
  '#type': textfield
  '#required': true
  '#title': EMAIL
  '#value': null
  '#minlength': 0
  '#id': useremail
  '#maxlength': 255
  '#default_value': null
  '#group_type': textfield
  '#visible': true
  '#class_name': user-email
rating_fit:
  '#type': select
  '#title': FIT
  '#required': false
  '#default_value': null
  '#options':
    1: 'Runs Small'
    2: 'True to Size'
    3: 'Runs Large'
  '#id': rating_Fit
  '#group_type': slider
  '#visible': true
  '#text': RATING
contextdatavalue_height_filter:
  '#type': select
  '#title': HEIGHT
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
  '#text': 'TELL US MORE ABOUT YOURSELF'
contextdatavalue_purchasesource:
  '#type': select
  '#title': 'Was this item purchased in a store or online?'
  '#required': false
  '#default_value': ''
  '#options':
    Instore: In-store
    Online: Online
  '#id': contextdatavalue_PurchaseSource
  '#group_type': select
  '#visible': true
contextdatavalue_reviewsread:
  '#type': select
  '#title': 'Did you read reviews prior to purchasing?'
  '#required': false
  '#default_value': ''
  '#options':
    'Yes': 'Yes'
    'No': 'No'
  '#id': contextdatavalue_ReviewsRead
  '#group_type': select
  '#visible': true
contextdatavalue_sizeorderedothers_filter:
  '#type': select
  '#title': 'SIZE ORDERED'
  '#required': false
  '#default_value': ''
  '#options':
    5: '5'
    512: '5 1/2'
    6: '6'
    612: '6 1/2'
    7: '7'
    712: '7 1/2'
    8: '8'
    812: '8 1/2'
    9: '9'
    912: '9 1/2'
    10: '10'
    11: '11'
    S: S
    M: M
    L: L
    XL: XL
    78: 7/8
    910: 9/10
    1112: 11/12
    36Us5: '36 (US 5)'
    37Us6: '37 (US 6)'
    38Us7: '38 (US 7)'
    39Us8: '39 (US 8)'
    40Us9: '40 (US 9)'
    41Us10: '41 (US 10)'
    42Us11: '42 US 11'
  '#id': contextdatavalue_SizeOrderedOthers_filter
  '#group_type': select
  '#visible': true
contextdatavalue_sizeorderedclothing_filter:
  '#type': select
  '#title': 'SIZE ORDERED'
  '#required': false
  '#default_value': ''
  '#options':
    26x28: 26x28
    26x30: 26x30
    28x28: 28x28
    28x30: 28x30
    28x32: 28x32
    28x34: 28x34
    29x30: 29x30
    29x32: 29x32
    29x34: 29x34
    30x30: 30x30
    30x32: 30x32
    30x34: 30x34
    30x36: 30x36
    31x30: 31x30
    31x32: 31x32
    31x34: 31x34
    32x30: 32x30
    32x32: 32x32
    32x34: 32x34
    32x36: 32x36
    33x30: 33x30
    33x32: 33x32
    33x34: 33x34
    34x30: 34x30
    34x32: 34x32
    34x34: 34x34
    34x36: 34x36
    36x30: 36x30
    36x32: 36x32
    36x34: 36x34
    36x36: 36x36
    38x30: 38x30
    38x32: 38x32
    38x34: 38x34
    38x36: 38x36
    40x30: 40x30
    40x32: 40x32
    40x34: 40x34
    42x32: 42x32
    42x34: 42x34
    44x32: 44x32
  '#id': contextdatavalue_SizeOrderedClothing_filter
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
netpromoterscore:
  '#type': textfield
  '#required': false
  '#title': 'On a scale from 0-10, how likely are you to recommend to a friend or family member?'
  '#value': null
  '#minlength': null
  '#id': netpromoterscore
  '#maxlength': 11
  '#default_value': null
  '#group_type': netpromoter
  '#visible': true
  '#text': ' '
sendemailalertwhencommented:
  '#type': checkbox
  '#required': false
  '#title': null
  '#value': 'false'
  '#id': sendemailalertwhencommented
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
hostedauthentication_rememberme:
  '#type': checkbox
  '#required': false
  '#title': null
  '#value': 'false'
  '#id': hostedauthentication_rememberme
  '#default_value': null
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
rating_fit:
  '#title': القياس
  '#default_value': null
  '#options':
    1: 'مقاس أصغر'
    2: 'مقاس فعلي'
    3: 'مقاس أكبر'
  '#text': تقييم
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
  '#text': 'اخبرنا المزيد عنك'
contextdatavalue_purchasesource:
  '#title': 'هل اشتريت المنتج من المحل أم عبر الموقع الإلكتروني؟'
  '#default_value': ''
  '#options':
    Instore: 'من المحل'
    Online: 'عبر الموقع الإلكتروني'
contextdatavalue_reviewsread:
  '#title': 'هل قرأت المراجعات قبل الشراء؟'
  '#default_value': ''
  '#options':
    'Yes': نعم
    'No': لا
contextdatavalue_sizeorderedothers_filter:
  '#title': 'المقاس المطلوب'
  '#default_value': ''
  '#options':
    5: '5'
    512: '5 1/2'
    6: '6'
    612: '6 1/2'
    7: '7'
    712: '7 1/2'
    8: '8'
    812: '8 1/2'
    9: '9'
    912: '9 1/2'
    10: '10'
    11: '11'
    S: S
    M: M
    L: L
    XL: XL
    78: 7/8
    910: 9/10
    1112: 11/12
    36Us5: '36 (US 5)'
    37Us6: '37 (US 6)'
    38Us7: '38 (US 7)'
    39Us8: '39 (US 8)'
    40Us9: '40 (US 9)'
    41Us10: '41 (US 10)'
    42Us11: '42 US 11'
contextdatavalue_sizeorderedclothing_filter:
  '#title': 'الحجم المطلوب'
  '#default_value': ''
  '#options':
    26x28: 26x28
    26x30: 26x30
    28x28: 28x28
    28x30: 28x30
    28x32: 28x32
    28x34: 28x34
    29x30: 29x30
    29x32: 29x32
    29x34: 29x34
    30x30: 30x30
    30x32: 30x32
    30x34: 30x34
    30x36: 30x36
    31x30: 31x30
    31x32: 31x32
    31x34: 31x34
    32x30: 32x30
    32x32: 32x32
    32x34: 32x34
    32x36: 32x36
    33x30: 33x30
    33x32: 33x32
    33x34: 33x34
    34x30: 34x30
    34x32: 34x32
    34x34: 34x34
    34x36: 34x36
    36x30: 36x30
    36x32: 36x32
    36x34: 36x34
    36x36: 36x36
    38x30: 38x30
    38x32: 38x32
    38x34: 38x34
    38x36: 38x36
    40x30: 40x30
    40x32: 40x32
    40x34: 40x34
    42x32: 42x32
    42x34: 42x34
    44x32: 44x32
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
netpromoterscore:
  '#title': 'على مقياس 0 -10، ما مدى احتمال أن توصي بنا لصديق أو أحد أفراد العائلة؟'
  '#default_value': null
  '#text': ' '
sendemailalertwhencommented:
  '#title': null
  '#default_value': null
hostedauthentication_authenticationemail:
  '#title': null
  '#default_value': null
sendemailalertwhenpublished:
  '#title': null
  '#default_value': null
hostedauthentication_rememberme:
  '#title': null
  '#default_value': null";

  return $content;
}
