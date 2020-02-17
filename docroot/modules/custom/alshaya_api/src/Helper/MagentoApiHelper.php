<?php

namespace Drupal\alshaya_api\Helper;

/**
 * Class MagentoApiHelper.
 *
 * Contains all reusable functions which don't require any other service.
 *
 * @see \Drupal\acq_commerce\APIHelper
 *
 * @package Drupal\alshaya_api\Helper
 */
class MagentoApiHelper {

  /**
   * Clean customer data before sending to API.
   *
   * @param array $customer
   *   Customer data.
   *
   * @return array
   *   Cleaned customer data.
   */
  public function cleanCustomerData(array $customer) {
    if (isset($customer['customer_id'])) {
      $customer['customer_id'] = (string) $customer['customer_id'];
    }

    if (isset($customer['addresses'])) {
      // When deleting an address need to re-index array.
      $customer['addresses'] = array_values($customer['addresses']);
      foreach ($customer['addresses'] as $delta => $address) {
        $address = (array) $address;
        $customer['addresses'][$delta] = $this->cleanCustomerAddress($address);
      }
    }

    return $customer;
  }

  /**
   * Get cleaned customer address.
   *
   * @param array $address
   *   Customer address.
   *
   * @return array
   *   Cleaned customer address.
   */
  public function cleanCustomerAddress(array $address) {
    if (isset($address['customer_address_id']) && empty($address['address_id'])) {
      $address['address_id'] = $address['customer_address_id'];
    }

    return $this->cleanAddress($address);
  }

  /**
   * Get cleaned cart address.
   *
   * @param mixed $address
   *   Cart address object/array.
   *
   * @return array
   *   Cleaned cart address.
   */
  public function cleanCartAddress($address) {
    $address = (array) $address;

    $address = $this->cleanAddress($address);

    if (isset($address['customer_address_id'])) {
      $address['customer_address_id'] = (int) $address['customer_address_id'];
    }

    // Never send address_id in API request, it confuses Magento.
    if (isset($address['address_id'])) {
      unset($address['address_id']);
    }

    return $address;
  }

  /**
   * Clean address (applicable for all type of addresses).
   *
   * @param array $address
   *   Address array.
   *
   * @return array
   *   Cleaned address array.
   */
  public function cleanAddress(array $address) {
    if (isset($address['customer_id'])) {
      $address['customer_id'] = (string) $address['customer_id'];
    }

    if (isset($address['default_billing'])) {
      $address['default_billing'] = (bool) $address['default_billing'];
    }

    if (isset($address['default_shipping'])) {
      $address['default_shipping'] = (bool) $address['default_shipping'];
    }

    return $address;
  }

  /**
   * Extensions must always be objects and not arrays.
   *
   * @param mixed $data
   *   Array/Object data.
   *
   * @return array
   *   Data in same type but with extension as object.
   */
  public function normaliseExtension($data) {
    if (is_object($data)) {
      if (isset($data->extension)) {
        $data->extension = (object) $data->extension;
      }
    }
    elseif (is_array($data)) {
      if (isset($data['extension'])) {
        $data['extension'] = (object) $data['extension'];
      }
    }

    return $data;
  }

}
