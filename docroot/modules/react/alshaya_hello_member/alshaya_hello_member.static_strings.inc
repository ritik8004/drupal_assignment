<?php

/**
 * @file
 * Contains Hello Member related static strings.
 */

/**
 * Hello Member static strings.
 */
function _alshaya_hello_member_static_strings() {
  return [
    [
      'key' => 'membership_title',
      'value' => t('Congrats!', [], ['context' => 'hello_member']),
    ],
    [
      'key' => 'membership_sub_title',
      'value' => t('You are now a H&M member.', [], ['context' => 'hello_member']),
    ],
    [
      'key' => 'membership_details',
      'value' => t('Get free delivery and 10% off on your first purchase.', [], ['context' => 'hello_member']),
    ],
    [
      'key' => 'qr_code_title',
      'value' => t('QR Code', [], ['context' => 'hello_member']),
    ],
    [
      'key' => 'view_qr_code',
      'value' => t('View QR code', [], ['context' => 'hello_member']),
    ],
    [
      'key' => 'qr_code_redeem',
      'value' => t('To redeem in store use this QR code.', [], ['context' => 'hello_member']),
    ],
  ];
}
