<?php

/**
 * @file
 * File to create bazaar voice content for BBW.
 */

/**
 * Bazaar Voice review form configurations for BBW.
 */
function alshaya_bbw_transac_bazaar_voice_content(): array {
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
  '#maxlength': 10000
  '#default_value': null
  '#group_type': textarea
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
tagid_like_packagingdesign:
  '#type': checkbox
  '#placeholder': 'What do you like more about the candle? Select all that apply.'
  '#required': false
  '#title': Packaging/Design
  '#value': null
  '#id': tagid_Like/PackagingDesign
  '#default_value': null
  '#group_type': tags
  '#visible': true
  '#text': Product
tagid_like_burnperformance:
  '#type': checkbox
  '#required': false
  '#title': 'Burn Performance'
  '#value': null
  '#id': tagid_Like/BurnPerformance
  '#default_value': null
  '#group_type': tags
  '#visible': true
tagid_like_fragranceappeal:
  '#type': checkbox
  '#required': false
  '#title': 'Fragrance Appeal'
  '#value': null
  '#id': tagid_Like/FragranceAppeal
  '#default_value': null
  '#group_type': tags
  '#visible': true
tagid_like_throwperformance:
  '#type': checkbox
  '#required': false
  '#title': 'Throw Performance'
  '#value': null
  '#id': tagid_Like/ThrowPerformance
  '#default_value': null
  '#group_type': tags
  '#visible': true
tagid_like_waxcolor:
  '#type': checkbox
  '#required': false
  '#title': 'Wax Color'
  '#value': null
  '#id': tagid_Like/WaxColor
  '#default_value': null
  '#group_type': tags
  '#visible': true
rating_quality:
  '#type': textfield
  '#required': true
  '#title': 'How would you rate the quality of this product?'
  '#value': null
  '#minlength': null
  '#id': rating_Quality
  '#maxlength': null
  '#default_value': null
  '#group_type': ratings
  '#visible': true
  '#text': 'Price value'
contextdatavalue_my_age_filter:
  '#type': select
  '#title': 'How old are you?'
  '#required': false
  '#default_value': ''
  '#options':
    1818: 'Under 18'
    1824: '18 - 24'
    2534: '25 - 34'
    3544: '35 - 44'
    4554: '45 - 54'
    5564: '55 - 64'
    Over65: 'Over 65'
  '#id': contextdatavalue_my_age_filter
  '#group_type': select
  '#visible': true
  '#text': Personal
contextdatavalue_wheredidyoupurchase:
  '#type': select
  '#title': 'Was this item purchased in a store or online?'
  '#required': false
  '#default_value': ''
  '#options':
    online: Online
    instore: 'In Store'
  '#id': contextdatavalue_WhereDidYouPurchase
  '#group_type': select
  '#visible': true
contextdatavalue_readreviews:
  '#type': select
  '#title': 'Did you read reviews prior to purchasing?'
  '#required': false
  '#default_value': ''
  '#options':
    'Yes': 'Yes'
    'No': 'No'
  '#id': contextdatavalue_ReadReviews
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
  '#visible': false";

  // Configuration in arabic language.
  $content['ar'] = "rating:
  '#title': 'التقييم الإجمالي'
  '#default_value': null
title:
  '#title': 'عنوان المراجعة'
  '#default_value': null
reviewtext:
  '#title': المراجعة
  '#default_value': null
photo_upload:
  '#text': |
    حمل الصورة

usernickname:
  '#title': اللقب
  '#default_value': null
  '#text': 'معلومات المستخدم الأساسية.'
useremail:
  '#title': 'البريد الإلكتروني'
  '#default_value': null
tagid_like_packagingdesign:
  '#placeholder': 'ما هو أكثر شيء تحبه في الشموع. حدد جميع الخيارات المطابقة'
  '#title': 'التغليف/ التصميم'
  '#default_value': null
  '#text': المنتج
tagid_like_burnperformance:
  '#title': 'الأداء عند الاحتراق'
  '#default_value': null
tagid_like_fragranceappeal:
  '#title': 'الرائحة العطرية'
  '#default_value': null
tagid_like_throwperformance:
  '#title': 'مدى انتشار الرائحة'
  '#default_value': null
tagid_like_waxcolor:
  '#title': 'لون الشمع'
  '#default_value': null
rating_quality:
  '#title': السعر
  '#default_value': null
  '#text': السعر
contextdatavalue_my_age_filter:
  '#title': العمر
  '#default_value': ''
  '#options':
    1818: 'أقل من 18'
    1824: '24 - 18'
    2534: '34 - 25'
    3544: '44 - 35'
    4554: '54 - 45'
    5564: '64 - 55'
    Over65: 'أكبر من 65'
  '#text': المستخدم
contextdatavalue_wheredidyoupurchase:
  '#title': 'هل اشتريت المنتج من المحل أم عبر الموقع الإلكتروني؟'
  '#default_value': ''
  '#options':
    online: 'من المحل'
    instore: 'عبر الموقع الإلكتروني'
contextdatavalue_readreviews:
  '#title': 'هل قرأت المراجعات قبل الشراء؟'
  '#default_value': ''
  '#options':
    'Yes': نعم
    'No': لا
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
hostedauthentication_authenticationemail:
  '#title': null
  '#default_value': null
hostedauthentication_rememberme:
  '#title': null
  '#default_value': null
sendemailalertwhenpublished:
  '#title': null
  '#default_value': null";

  return $content;
}
