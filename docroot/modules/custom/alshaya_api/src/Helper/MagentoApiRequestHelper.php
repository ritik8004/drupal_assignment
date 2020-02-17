<?php

namespace Drupal\alshaya_api\Helper;

/**
 * Class MagentoApiRequestHelper.
 *
 * @package Drupal\alshaya_api\Helper
 */
class MagentoApiRequestHelper {

  /**
   * Process customer data to make it magento api request compatible.
   *
   * @param array $customer
   *   The array of customer data.
   *
   * @return array
   *   Return processed array.
   */
  public static function prepareCustomerDataForApi(array $customer) {
    $query['customer'] = [
      'id' => $customer['customer_id'] ?? NULL,
    ];

    foreach (['email', 'firstname', 'lastname', 'dob'] as $field) {
      if (!empty($customer[$field])) {
        $query['customer'][$field] = $customer[$field];
      }
    }

    if (!empty($customer['title'])) {
      $query['customer']['prefix'] = $customer['title'];
    }

    // Browse all the addresses and normalize.
    if (!empty($customer['addresses'])) {
      $query['customer']['addresses'] = [];

      $addresses = $customer['addresses'];
      if (is_array($addresses)) {
        foreach ($addresses as $address) {
          $query['customer']['addresses'][] = self::addressFromEntity($address);
        }
      }
    }

    if (($customAttrs = $customer['extension']) && count($customAttrs)) {
      $attrData = [];
      foreach ($customAttrs as $key => $value) {
        $attrData[] = [
          'attribute_code' => $key,
          'value' => $value,
        ];
      }

      $query['customer']['custom_attributes'] = $attrData;
    }

    if (isset($customer['newsletter_subscribed'])) {
      $query['customer']['extension_attributes']['is_subscribed'] = (bool) $customer['newsletter_subscribed'];
    }

    return $query['customer'];
  }

  /**
   * Convert address from entity to magento api request compatible.
   *
   * @param array $address
   *   Array of address.
   *
   * @return array
   *   Return processed array of address.
   */
  public static function addressFromEntity(array $address) {
    $mageAddress = [
      'id'               => $address['address_id'] ?? NULL,
      'prefix'           => $address['title'] ?? NULL,
      'firstname'        => $address['firstname'],
      'lastname'         => $address['lastname'],
      'street'           => [
        $address['street'] ?? '',
        $address['street2'] ?? '',
      ],
      'city'             => $address['city'] ?? NULL,
      'region_id'        => $address['region_id'] ?? NULL,
      'region'           => $address['region'] ?? NULL,
      'postcode'         => $address['postcode'] ?? NULL,
      'country_id'       => $address['country_id'],
      'telephone'        => $address['telephone'] ?? NULL,
      'default_shipping' => $address['default_shipping'] ?? FALSE,
      'default_billing'  => $address['default_billing'] ?? FALSE,
    ];
    if ($mageAddress['id'] === 0) {
      unset($mageAddress['id']);
    }
    if (empty($mageAddress['region_id'])) {
      unset($mageAddress['region_id']);
    }
    if (empty($mageAddress['default_shipping'])) {
      unset($mageAddress['default_shipping']);
    }
    if (empty($mageAddress['default_billing'])) {
      unset($mageAddress['default_billing']);
    }

    // Manage the custom attributes from the addresses.
    if (isset($address['extension'])) {
      $attrData = [];
      foreach ($address['extension'] as $key => $value) {
        $attrData[] = [
          'attributeCode' => $key,
          'value' => $value,
        ];
      }
      $mageAddress['customAttributes'] = $attrData;
    }
    return $mageAddress;
  }

}
