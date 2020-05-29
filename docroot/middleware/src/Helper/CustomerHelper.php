<?php

namespace App\Helper;

/**
 * Class CustomerHelper.
 *
 * @package App\Helper
 */
class CustomerHelper {

  /**
   * Helper function to format address as required by frontend.
   *
   * @param array $address
   *   Address array.
   *
   * @return array|null
   *   Formatted address if available.
   */
  public static function formatAddressForFrontend(array $address) {
    // Do not consider addresses without custom attributes as they are required
    // for Delivery Matrix.
    if (empty($address) || empty($address['custom_attributes'])) {
      return NULL;
    }

    $customAttributes = [];
    foreach ($address['custom_attributes'] ?? [] as $value) {
      $customAttributes[$value['attribute_code']] = $value['value'];
    }

    unset($address['custom_attributes']);
    $address += $customAttributes;

    return $address;
  }

  /**
   * Helper function to get clean customer data.
   *
   * @param array $customer
   *   Customer data.
   *
   * @return array
   *   Clean customer array.
   */
  public static function getCustomerPublicData(array $customer) {
    if (empty($customer)) {
      return [];
    }

    $data['id'] = $customer['id'] ?? 0;
    $data['firstname'] = $customer['firstname'] ?? '';
    $data['lastname'] = $customer['lastname'] ?? '';
    $data['email'] = $customer['email'] ?? '';

    $data['addresses'] = [];
    foreach ($customer['addresses'] ?? [] as $address) {
      $data['addresses'][] = static::formatAddressForFrontend($address);
    }

    return $data;
  }

}
