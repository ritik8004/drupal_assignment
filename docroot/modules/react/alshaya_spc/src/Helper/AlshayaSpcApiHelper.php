<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;

/**
 * Class AlshayaSpcApiHelper.
 */
class AlshayaSpcApiHelper {

  /**
   * The api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * AlshayaSpcApiHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   The api wrapper.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper
  ) {
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * Function to get customer address form.
   *
   * @param string $mail
   *   The mail address.
   * @param string $pass
   *   The customer password.
   *
   * @return array
   *   The Form array from API response OR empty array.
   */
  public function authenticateCustomerOnMagento(string $mail, string $pass) {
    $endpoint = 'customers/by-login-and-password';

    try {
      $response = $this->apiWrapper->invokeApi(
        $endpoint,
        [
          'username' => $mail,
          'password' => $pass,
        ],
        'JSON'
      );
    }
    catch (\Exception $e) {
      return [];
    }

    if ($response && is_string($response)) {
      $response = json_decode($response, TRUE);
      // Move the cart_id into the customer object.
      if (isset($response['cart_id'])) {
        $response['customer']['custom_attributes'][] = [
          'attribute_code' => 'cart_id',
          'value' => $response['cart_id'],
        ];
      }
      return self::customerFromSearchResult($response['customer']);

    }

    return [];
  }

  /**
   * Prepare Customer data same as ACM.
   *
   * To avoid code changes in existing module like alshaya_acm_customer.
   *
   * @param array $customer
   *   The array of customer.
   *
   * @return array
   *   Return processed array.
   */
  public static function customerFromSearchResult(array $customer) {
    $mage_addresses = ($customer['addresses'] ?? []);
    $addresses = array_map(
      function ($mage_address) {
        return self::addressFromSearchResult($mage_address);
      },
      $mage_addresses
    );
    $extension = [];
    foreach (($customer['custom_attributes'] ?? []) as $attr) {
      if (
        (!strlen(($attr['attribute_code'] ?? ''))) ||
        (!strlen(($attr['value'] ?? '')))
      ) {
        continue;
      }
      $extension[$attr['attribute_code']] = $attr['value'];
    }
    return [
      'customer_id' => (int) ($customer['id'] ?? 0),
      'store_id' => (int) ($customer['store_id'] ?? 0),
      'group_id' => (int) ($customer['group_id'] ?? 0),
      'email'  => (string) ($customer['email'] ?? ''),
      'firstname' => (string) ($customer['firstname'] ?? ''),
      'lastname' => (string) ($customer['lastname'] ?? ''),
      'title' => (string) ($customer['prefix'] ?? ''),
      'dob' => (string) ($customer['dob'] ?? ''),
      'created' => (string) ($customer['created_at'] ?? ''),
      'updated' => (string) ($customer['updated_at'] ?? ''),
      'addresses' => $addresses,
      'extension' => $extension,
    ];
  }

  /**
   * Prepare address array.
   *
   * @param array $address
   *   The array of address.
   *
   * @return array
   *   The array of prepared address.
   */
  public static function addressFromSearchResult(array $address) {
    $mage_region = ($address['region'] ?? '');
    if (is_array($mage_region)) {
      $mage_region = (string) ($mage_region['region'] ?? '');
    }
    // Move the custom_attributes to extension.
    $extension = [];
    foreach (($address['custom_attributes'] ?? []) as $attr) {
      if (
        (!strlen(($attr['attribute_code'] ?? ''))) ||
        (!strlen(($attr['value'] ?? '')))
      ) {
        continue;
      }
      $extension[$attr['attribute_code']] = $attr['value'];
    }
    // In case of user orders, we don't get custom_attributes.
    if (!empty($address['extension_attributes'])) {
      foreach ($address['extension_attributes'] as $attr_key => $attr_value) {
        $extension[$attr_key] = $attr_value;
      }
    }
    return [
      'address_id' => (int) ($address['id'] ?? 0),
      'title' => (string) ($address['prefix'] ?? ''),
      'firstname' => (string) ($address['firstname'] ?? ''),
      'lastname' => (string) ($address['lastname'] ?? ''),
      'street' => (string) ($address['street'][0] ?? ''),
      'street2' => (string) ($address['street'][1] ?? ''),
      'city' => (string) ($address['city'] ?? ''),
      'region' => (string) $mage_region,
      'region_id' => (int) ($address['region_id'] ?? ''),
      'postcode' => (string) ($address['postcode'] ?? ''),
      'country_id' => (string) ($address['country_id'] ?? ''),
      'telephone' => (string) ($address['telephone'] ?? ''),
      'default_billing' => (bool) ($address['default_billing'] ?? FALSE),
      'default_shipping' => (bool) ($address['default_shipping'] ?? FALSE),
      'customer_address_id' => (int) ($address['customer_address_id'] ?? 0),
      'customer_id' => (int) ($address['customer_id'] ?? 0),
      'extension' => $extension,
    ];
  }

}
