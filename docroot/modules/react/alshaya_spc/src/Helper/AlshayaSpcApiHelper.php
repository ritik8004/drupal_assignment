<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\acq_commerce\Conductor\RouteException;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Serialization\Json;
use Drupal\alshaya_api\Helper\MagentoApiHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;

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
   * The mdc helper.
   *
   * @var \Drupal\alshaya_api\Helper\MagentoApiHelper
   */
  protected $mdcHelper;

  /**
   * Th module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaSpcApiHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   The api wrapper.
   * @param \Drupal\alshaya_api\Helper\MagentoApiHelper $mdc_helper
   *   The magento api helper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    MagentoApiHelper $mdc_helper,
    ModuleHandlerInterface $module_handler
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->mdcHelper = $mdc_helper;
    $this->moduleHandler = $module_handler;
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
      $response = Json::decode($response, TRUE);
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
   * Get customer by email, helpful for logged in user.
   *
   * @param string $email
   *   The email id.
   *
   * @return array
   *   Return array of customer data or empty array.
   *
   * @throws \Exception
   */
  public function getCustomer($email) {
    $query_string_values = [
      'condition_type' => 'eq',
      'field' => 'email',
      'value' => $email,
    ];
    $query_string_array = [];
    foreach ($query_string_values as $key => $value) {
      $query_string_array["searchCriteria[filterGroups][0][filters][0][{$key}]"] = $value;
    }

    $customer = [];
    try {
      $response = $this->apiWrapper->invokeApi("customers/search", $query_string_array, 'GET');
      $result = Json::decode($response);
      if (!empty($result['items'])) {
        $customer = $this->customerFromSearchResult(reset($result['items']));
        $customer = $this->mdcHelper->cleanCustomerData($customer);
      }
    }
    catch (\Exception $e) {
      throw new \Exception($e->getMessage(), $e->getCode(), $e);
    }

    return $customer;
  }

  /**
   * Update customer with details.
   *
   * @param array $customer
   *   The array of customer details.
   * @param array $options
   *   The options.
   *
   * @return array|mixed
   *   Return array of customer details or null.
   */
  public function updateCustomer(array $customer, array $options = []) {
    $opt['json']['customer'] = $customer;

    if (isset($options['password']) && !empty($options['password'])) {
      $opt['json']['password'] = $options['password'];
    }

    if (isset($options['password_old']) && !empty($options['password_old'])) {
      $opt['json']['password_old'] = $options['password_old'];
    }

    if (isset($options['password_token']) && !empty($options['password_token'])) {
      $opt['json']['password_token'] = $options['password_token'];
    }

    if (isset($options['access_token']) && !empty($options['access_token'])) {
      $opt['json']['token'] = $options['access_token'];
    }

    // Invoke the alter hook to allow all modules to update the customer data.
    $this->moduleHandler->alter('acq_commerce_update_customer_api_request', $opt);

    // Do some cleanup.
    $opt['json']['customer'] = $this->mdcHelper->cleanCustomerData($opt['json']['customer']);
    $opt['json']['customer'] = MagentoApiHelper::prepareCustomerDataForApi($opt['json']['customer']);

    $endpoint = 'customers';
    $method = 'JSON';
    if (!empty($opt['json']['customer']['id'])) {
      $endpoint .= '/' . $opt['json']['customer']['id'];
      $method = 'PUT';
    }

    try {
      $response = $this->apiWrapper->invokeApi($endpoint, $opt['json'], $method);
      $response = Json::decode($response);
      if (!empty($response)) {
        // Move the cart_id into the customer object.
        if (isset($response['cart_id'])) {
          $response['custom_attributes'][] = [
            'attribute_code' => 'cart_id',
            'value' => $response['cart_id'],
          ];
        }
        $response = self::customerFromSearchResult($response);
      }
    }
    catch (ConnectorException $e) {
      throw new RouteException(__FUNCTION__, $e->getMessage(), $e->getCode(), $this->getRouteEvents());
    }

    return $response;
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
