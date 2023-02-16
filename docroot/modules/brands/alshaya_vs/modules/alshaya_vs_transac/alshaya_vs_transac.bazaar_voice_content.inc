<?php

/**
 * @file
 * File to create bazaar voice content for VS.
 */

/**
 * Bazaar Voice review form configurations for VS.
 */
function alshaya_vs_transac_bazaar_voice_content(): array {
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
rating_comfort_vs:
  '#type': select
  '#title': Comfort
  '#required': false
  '#default_value': null
  '#options':
    1: Poor
    2: 'Below Average'
    3: Average
    4: 'Above Average'
    5: Excellent
  '#id': rating_comfort_vs
  '#group_type': slider
  '#visible': true
rating_quality_vs:
  '#type': select
  '#title': Quality
  '#required': false
  '#default_value': null
  '#options':
    1: Poor
    2: 'Below Average'
    3: Average
    4: 'Above Average'
    5: Excellent
  '#id': rating_quality_vs
  '#group_type': slider
  '#visible': true
rating_fit:
  '#type': select
  '#title': Fit
  '#required': false
  '#default_value': null
  '#options':
    1: 'Runs Small'
    2: 'Below True to Size'
    3: 'True To Size'
    4: 'Above True to Size'
    5: 'Runs Large'
  '#id': rating_Fit
  '#group_type': slider
  '#visible': true
rating_scent_longevity:
  '#type': select
  '#title': 'Scent Longevity'
  '#required': false
  '#default_value': null
  '#options':
    1: Poor
    2: 'Below Average'
    3: Average
    4: 'Above Average'
    5: Excellent
  '#id': rating_ScentLongevity
  '#group_type': slider
  '#visible': true
rating_scent_intensity:
  '#type': select
  '#title': 'Scent Intensity'
  '#required': false
  '#default_value': null
  '#options':
    1: Subtle
    2: 'Below Average'
    3: Average
    4: 'Above Average'
    5: Intense
  '#id': rating_ScentIntensity
  '#group_type': slider
  '#visible': true
rating_value_vs:
  '#type': select
  '#title': Value
  '#required': false
  '#default_value': null
  '#options':
    1: Poor
    2: 'Below Average'
    3: Average
    4: 'Above Average'
    5: Excellent
  '#id': rating_value_vs
  '#group_type': slider
  '#visible': true
rating_effectiveness:
  '#type': select
  '#title': Effectiveness
  '#required': false
  '#default_value': null
  '#options':
    1: Poor
    2: 'Below Average'
    3: Average
    4: 'Above Average'
    5: Excellent
  '#id': rating_Effectiveness
  '#group_type': slider
  '#visible': true
rating_expectation:
  '#type': select
  '#title': Expectation
  '#required': false
  '#default_value': null
  '#options':
    1: 'Did Not Meet Expectation'
    2: 'Less than Expectation'
    3: 'Met Expectation'
    4: 'Above Expectation'
    5: 'Exceeded Expectation'
  '#id': rating_Expectation
  '#group_type': slider
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
  '#maxlength': 10000
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
contextdatavalue_age:
  '#type': select
  '#title': 'How old are you?'
  '#required': false
  '#default_value': ''
  '#options':
    17orUnder: '17 or less'
    18to24: 18-24
    25to34: 25-34
    35to44: 35-44
    45to54: 45-54
    55to64: 55-64
    65orOver: 'Over 64'
  '#id': contextdatavalue_Age
  '#group_type': select
  '#text': Personal
  '#visible': true
contextdatavalue_height:
  '#type': select
  '#title': Height
  '#required': false
  '#default_value': ''
  '#options':
    50OrLess: '5''0 or less'
    5153: '5''1 - 5''3'
    5456: '5''4 - 5''6'
    5759: '5''7 - 5''9'
    51060: '5''10 - 6''0'
    6163: '6''1 - 6''3'
    6466: '6''4 - 6''6'
    67OrAbove: '6''7 or above'
  '#id': contextdatavalue_Height
  '#group_type': select
  '#visible': true
contextdatavalue_staffreview:
  '#type': select
  '#title': 'Do you work for Victorias Secret?'
  '#required': false
  '#default_value': ''
  '#options':
    'Yes': 'Yes'
    'No': 'No'
  '#id': contextdatavalue_StaffReview
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
  '#title': 'التقييم العام'
  '#default_value': null
rating_comfort_vs:
  '#title': 'الراحة'
  '#default_value': null
  '#options':
    1: 'مريح'
    2: 'أقل من المتوسط'
    3: 'متوسط'
    4: 'أعلى من المتوسط'
    5: 'ممتاز'
rating_quality_vs:
  '#title': 'الجودة'
  '#default_value': null
  '#options':
    1: 'مريح'
    2: 'أقل من المتوسط'
    3: 'متوسط'
    4: 'أعلى من المتوسط'
    5: 'ممتاز'
rating_fit:
  '#title': 'لائق بدنيا'
  '#default_value': null
  '#options':
    1: 'أصغر من مقاسي'
    2: 'أقل من المقاس الحقيقي'
    3: 'نفس مقاسي'
    4: 'أعلى من المقاس الحقيقي'
    5: 'أكبر من مقاسي'
rating_scent_longevity:
  '#title': 'ثبات العطر'
  '#default_value': null
  '#options':
    1: 'مريح'
    2: 'أقل من المتوسط'
    3: 'ثابت'
    4: 'أعلى من المتوسط'
    5: 'يدوم طويلاً'
rating_scent_intensity:
  '#title': 'قوة العطر'
  '#default_value': null
  '#options':
    1: 'خفيف'
    2: 'أقل من المتوسط'
    3: 'متوسط'
    4: 'أعلى من المتوسط'
    5: 'قوي'
rating_value_vs:
  '#title': 'القيمة'
  '#default_value': null
  '#options':
    1: 'مريح'
    2: 'أقل من المتوسط'
    3: 'متوسط'
    4: 'أعلى من المتوسط'
    5: 'ممتاز'
rating_effectiveness:
  '#title': 'فعالية المنتج'
  '#default_value': null
  '#options':
    1: 'مريح'
    2: 'أقل من المتوسط'
    3: 'متوسط'
    4: 'أعلى من المتوسط'
    5: 'ممتاز'
rating_expectation:
  '#title': 'التوقعات'
  '#default_value': null
  '#options':
    1: 'لم يلبِ التوقعات'
    2: 'أقل من المتوقع'
    3: 'لَبَّى التوقعات'
    4: 'أعلى من المتوقع'
    5: 'فاق التوقعات'
isrecommended:
  '#title': 'هل يمكنك توصية هذا المنتج لصديق؟'
  '#value': null
title:
  '#title': 'عنوان مراجعة'
  '#value': null
reviewtext:
  '#title': 'مراجعة'
  '#value': null
photo_upload:
  '#text': |
    حمل الصورة
usernickname:
  '#title': 'اللقب'
  '#value': null
  '#text': 'معلومات المستخدم الأساسية.'
useremail:
  '#title': 'البريد الإلكتروني'
  '#value': null
contextdatavalue_age:
  '#title': 'كم عمرك؟'
  '#default_value': ''
  '#options':
    17orUnder: '17 أو أقل'
    18to24: 18-24
    25to34: 25-34
    35to44: 35-44
    45to54: 45-54
    55to64: 55-64
    65orOver: 'أكبر من 64'
  '#text': المستخدم
contextdatavalue_height:
  '#title': 'الطول'
  '#default_value': ''
  '#options':
    50OrLess: '5 قدم أو أقل'
    5153: '5''1 - 5''3'
    5456: '5''4 - 5''6'
    5759: '5''7 - 5''9'
    51060: '5''10 - 6''0'
    6163: '6''1 - 6''3'
    6466: '6''4 - 6''6'
    67OrAbove: '6 قدم و7 بوصة أو أكثر'
contextdatavalue_staffreview:
  '#title': 'هل تعمل في فيكتوريا سيكريت؟'
  '#default_value': ''
  '#options':
    'Yes': 'نعم'
    'No': 'لا'
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
    OM: 'عُمان'";

  return $content;
}
