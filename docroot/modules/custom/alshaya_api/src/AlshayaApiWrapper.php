<?php

namespace Drupal\alshaya_api;

use Drupal\alshaya_stores_finder\StoresFinderUtility;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;

/**
 * Class AcqPromotionsManager.
 */
class AlshayaApiWrapper {

  /**
   * Stores the alshaya_api settings config array.
   *
   * @var array
   */
  protected $config;

  /**
   * Token to access APIs.
   *
   * @var string
   */
  protected $token;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Language code.
   *
   * @var string
   */
  protected $langcode;

  /**
   * Store finder utility object.
   *
   * @var object
   */
  protected $storeUtility;

  /**
   * Address Book Manager object.
   *
   * @var object
   */
  protected $addressBookManager;

  /**
   * Constructs a new AlshayaApiWrapper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->config = $config_factory->get('alshaya_api.settings');
    $this->languageManager = $language_manager;
    $this->langcode = $language_manager->getCurrentLanguage()->getId();
    $this->logger = $logger_factory->get('alshaya_api');
  }

  /**
   * Set store finder utility when available.
   *
   * @param \Drupal\alshaya_api\StoresFinderUtility $stores_utility
   *   The store finder utility object.
   */
  public function setStoreFinderUtility(StoresFinderUtility $stores_utility) {
    $this->storeUtility = $stores_utility;
  }

  /**
   * Set Address book manager when available.
   *
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $addressBookManager
   *   The address book manager object.
   */
  public function setAddressBookManager(AlshayaAddressBookManager $addressBookManager) {
    $this->addressBookManager = $addressBookManager;
  }

  /**
   * Function to override context langcode for API calls.
   *
   * @param string $langcode
   *   Language code to use for API calls.
   */
  public function updateStoreContext($langcode) {
    // Calling code will be responsible for doing all checks on the value.
    $this->langcode = $langcode;
  }

  /**
   * API to create transaction entry for K-Net.
   *
   * @param string $order_id
   *   Order increment id.
   * @param string $transaction_id
   *   K-Net transaction id.
   * @param string $auth
   *   K-Net auth.
   *
   * @return mixed
   *   Response of API or NULL.
   */
  public function addKnetTransaction($order_id, $transaction_id, $auth) {
    $endpoint = 'knet/transaction';

    $data = [
      'orderId' => $order_id,
      'transactionId' => $transaction_id,
      'authCode' => $auth,
    ];

    try {
      return $this->invokeApi($endpoint, $data, 'JSON');
    }
    catch (\Exception $e) {
      $this->logger->critical('Error occurred while adding transaction for knet order: %info <br> %message', [
        '%info' => print_r($data, TRUE),
        '%message' => $e->getMessage(),
      ]);
    }

    return NULL;
  }

  /**
   * Function to get all the stores from the API.
   *
   * @param string $langcode
   *   Language code.
   *
   * @return mixed
   *   Stores array.
   */
  public function getStores($langcode) {
    $this->updateStoreContext($langcode);

    $endpoint = 'storeLocator/search?searchCriteria=';

    $response = $this->invokeApi($endpoint, [], 'GET');

    $stores = json_decode($response, TRUE);

    return $stores;
  }

  /**
   * Function to sync all stores.
   */
  public function syncStores() {
    // Do API call to get stores for each language.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      // Get all stores for particular language.
      $stores = $this->getStores($langcode);

      if (!empty($stores['items'])) {
        // Loop through all the stores and add/edit/translate the store node.
        foreach ($stores['items'] as $store) {
          $this->storeUtility->updateStore($store, $langcode);
        }
      }
    }
  }

  /**
   * Function to get stores for a product variant near the user's location.
   *
   * @param string $sku
   *   Product SKU.
   * @param float $lat
   *   Latitude.
   * @param float $lon
   *   Longitude.
   *
   * @return array
   *   Stores array.
   */
  public function getSkuStores($sku, $lat, $lon) {
    $stores = $this->getProductStores($sku, $lat, $lon);

    // Add missing information to store data.
    array_walk($stores, function (&$store) use (&$index) {
      $store['rnc_available'] = (int) $store['rnc_available'];
      $store['sts_available'] = (int) $store['sts_available'];
      $store['sequence'] = $index++;

      if ($store_node = $this->storeUtility->getTranslatedStoreFromCode($store['code'])) {
        $extra_data = $this->storeUtility->getStoreExtraData($store, $store_node);
        $store = array_merge($store, $extra_data);
      }
    });

    // Sort the stores first by distance and then by name.
    alshaya_master_utility_usort($stores, 'rnc_available', 'desc', 'distance', 'asc');

    if (\Drupal::moduleHandler()->moduleExists('alshaya_click_collect')) {
      $cc_config = \Drupal::config('alshaya_click_collect.settings');
    }

    // Add sequence and proper delivery_time label and low stock text.
    foreach ($stores as $index => $store) {
      $stores[$index]['sequence'] = $index + 1;

      // Display configured value for rnc else sts delivery time.
      $time = $store['rnc_available'] ? ($cc_config) ?: $cc_config->get('click_collect_rnc') : $store['sts_delivery_time_label'];
      $stores[$index]['delivery_time'] = t('Collect from store in <em>@time</em>', ['@time' => $time]);
      $stores[$index]['low_stock_text'] = $store['low_stock'] ? t('Low stock') : '';
    }

    return $stores;
  }

  /**
   * Function to get click and collect stores available nearby for a product.
   *
   * @param string $sku
   *   String SKU.
   * @param float $lat
   *   Latitude of user.
   * @param float $lon
   *   Longitude of user.
   *
   * @return mixed
   *   Response from the API.
   */
  public function getProductStores($sku, $lat, $lon) {
    if (\Drupal::state()->get('store_development_mode', 0)) {
      $lat = 29;
      $lon = 48;
    }

    $sku = urlencode($sku);

    $endpoint = 'click-and-collect/stores/product/' . $sku . '/lat/' . $lat . '/lon/' . $lon;
    $response = $this->invokeApi($endpoint, [], 'GET');
    $stores = json_decode($response, TRUE);
    return $stores;
  }

  /**
   * Function to get click and collect stores available nearby for a cart.
   *
   * @param int $cart_id
   *   The cart ID.
   * @param float $lat
   *   Latitude of user.
   * @param float $lon
   *   Longitude of user.
   *
   * @return mixed
   *   Response from the API.
   */
  public function getCartStores($cart_id, $lat = NULL, $lon = NULL) {
    if (\Drupal::state()->get('store_development_mode', 0)) {
      $lat = 29;
      $lon = 48;
    }

    $endpoint = 'click-and-collect/stores/cart/' . $cart_id . '/lat/' . $lat . '/lon/' . $lon;
    $response = $this->invokeApi($endpoint, [], 'GET');
    $stores = json_decode($response, TRUE);
    if (is_array($stores) && !empty($stores['message'])) {
      return [];
    }

    return $stores;
  }

  /**
   * Function to invoke the API and get response.
   *
   * Note: GET parameters must be handled in invoking function itself.
   *
   * @param string $endpoint
   *   Endpoint URL, specific for the API call.
   * @param array $data
   *   Post data to send to API.
   * @param string $method
   *   GET or POST.
   * @param bool $requires_token
   *   Flag to specify if this API requires token or not.
   *
   * @return mixed
   *   Response from the API.
   */
  public function invokeApi($endpoint, array $data = [], $method = 'POST', $requires_token = TRUE) {
    $url = $this->config->get('magento_host');
    $url .= '/' . $this->getMagentoLangPrefix();
    $url .= '/' . $this->config->get('magento_api_base');
    $url .= '/' . $endpoint;

    $header = [];

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    if ($requires_token) {
      try {
        $token = $this->getToken();
      }
      catch (\Exception $e) {
        return [];
      }

      $header[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->config->get('verify_ssl'));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->config->get('verify_ssl'));

    if ($method == 'POST') {
      curl_setopt($curl, CURLOPT_POST, TRUE);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    elseif ($method == 'JSON') {
      $data_string = json_encode($data);

      $header[] = 'Content-Type: application/json';
      $header[] = 'Content-Length: ' . strlen($data_string);

      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($curl, CURLOPT_POST, TRUE);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
  }

  /**
   * Function to get token to access Magento APIs.
   *
   * @return string
   *   Token as string.
   */
  private function getToken() {
    if ($this->token) {
      return $this->token;
    }

    $cid = 'alshaya_api_token';

    if ($cache = \Drupal::cache('data')->get($cid)) {
      $this->token = $cache->data;
    }
    else {
      $endpoint = 'integration/admin/token';

      $data = [];
      $data['username'] = $this->config->get('username');
      $data['password'] = $this->config->get('password');

      $token = $this->invokeApi($endpoint, $data, 'POST', FALSE);

      $response = json_decode($token, TRUE);
      if (is_array($response) && isset($response['message'])) {
        $this->logger->critical('Unable to get token from magento');
        throw new \Exception('Unable to get token from magento');
      }

      $this->token = str_replace('"', '', $token);

      // Calculate the timestamp when we want the cache to expire.
      $expire = \Drupal::time()->getRequestTime() + $this->config->get('token_cache_time');

      // Set the stock in cache.
      \Drupal::cache('data')->set($cid, $this->token, $expire);
    }

    return $this->token;
  }

  /**
   * Function to get locations for delivery matrix.
   *
   * @param string $filterField
   *   The field name to filter on.
   * @param string $filterValue
   *   The value of the field to filter on.
   *
   * @return mixed
   *   Response from API.
   */
  public function getLocations($filterField = 'attribute_id', $filterValue = 'governate') {
    $endpoint = 'deliverymatrix/address-locations/search?searchCriteria[filter_groups][0][filters][0][field]=' . $filterField . '&searchCriteria[filter_groups][0][filters][0][value]=' . $filterValue . '&searchCriteria[filter_groups][0][filters][0][condition_type]=eq';
    $response = $this->invokeApi($endpoint, [], 'GET', FALSE);
    $locations = json_decode($response, TRUE);
    if (is_array($locations) && !empty($locations)) {
      return $locations;
    }
    return [];
  }

  /**
   * Function to sync areas for delivery matrix.
   */
  public function syncAreas() {
    $governates = $this->getLocations('attribute_id', 'governate');
    if (isset($governates['items']) && !empty($governates['items'])) {
      $termsProcessed = [];
      foreach ($governates['items'] as $governate) {
        // Assuming Parent ID is 0, for Governates.
        $governateData = [
          'name' => $governate['labels'][0],
          'field_location_id' => $governate['location_id'],
        ];
        /** @var \Drupal\taxonomy\Entity\Term $governateTerm */
        $governateTerm = $this->addressBookManager->updateLocation($governateData);
        $termsProcessed[] = $governate['labels'][0];

        // Fetch area's under this governate.
        $areas = $this->getLocations('parent_id', $governate['location_id']);
        if (isset($areas['items']) && !empty($areas['items'])) {
          foreach ($areas['items'] as $area) {
            $areaData = [
              'name' => $area['labels'][0],
              'field_location_id' => $area['location_id'],
              'parent' => $governateTerm->id(),
            ];
            $this->addressBookManager->updateLocation($areaData);
            $termsProcessed[] = $area['labels'][0];
          }
        }
      }

      // Delete the excess terms that exist.
      if (!empty($termsProcessed)) {
        $result = \Drupal::entityQuery('taxonomy_term')
          ->condition('vid', 'area_list')
          ->condition('name', $termsProcessed, 'NOT IN')
          ->execute();

        if ($result) {
          $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
          $terms = $term_storage->loadMultiple($result);
          $term_storage->delete($terms);
        }
      }

    }
  }

  /**
   * Method to provide the Language prefix for magento, per language.
   *
   * @return string
   *   The Language prefix for current language.
   */
  private function getMagentoLangPrefix() {
    $mapping = [];

    $languages = \Drupal::languageManager()->getLanguages();

    // Prepare the alternate locale data.
    foreach ($languages as $lang => $language) {
      // For default language, we access the config directly.
      if ($lang == \Drupal::languageManager()->getDefaultLanguage()->getId()) {
        $config = \Drupal::config('alshaya_api.settings');
      }
      // We get store id from translated config for other languages.
      else {
        $config = \Drupal::languageManager()->getLanguageConfigOverride($lang, 'alshaya_api.settings');
      }

      $mapping[$lang] = $config->get('magento_lang_prefix');
    }

    return $mapping[$this->langcode];
  }

}
