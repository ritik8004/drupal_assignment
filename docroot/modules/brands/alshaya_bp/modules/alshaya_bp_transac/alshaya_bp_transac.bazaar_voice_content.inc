<?php

/**
 * @file
 * File to create bazaar voice content for Boots.
 */

/**
 * Bazaar Voice review form configurations for Boots.
 */
function alshaya_bp_transac_bazaar_voice_content(): array {
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
sendemailalertwhenpublished:
  '#type': checkbox
  '#required': false
  '#title': null
  '#value': 'false'
  '#id': sendemailalertwhenpublished
  '#default_value': false
  '#group_type': boolean
  '#visible': false
sendemailalertwhencommented:
  '#type': checkbox
  '#required': false
  '#title': null
  '#value': 'false'
  '#id': sendemailalertwhencommented
  '#default_value': false
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
