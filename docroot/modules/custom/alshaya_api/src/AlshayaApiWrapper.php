<?php

namespace Drupal\alshaya_api;

use Drupal\alshaya_stores_finder\StoresFinderUtility;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class AcqPromotionsManager.
 */
class AlshayaApiWrapper {

  use StringTranslationTrait;

  /**
   * Stores the alshaya_api settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Stores the click_collect settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $clickCollectSettings;

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
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * Cache Backend object for "cache.data".
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs a new AlshayaApiWrapper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend object for "cache.data".
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              TimeInterface $date_time,
                              CacheBackendInterface $cache,
                              ModuleHandlerInterface $module_handler,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->config = $config_factory->get('alshaya_api.settings');

    // Initialise click and collect settings only if module enabled.
    if ($module_handler->moduleExists('alshaya_click_collect')) {
      $this->clickCollectSettings = $config_factory->get('alshaya_click_collect.settings');
    }

    $this->logger = $logger_factory->get('alshaya_api');
    $this->languageManager = $language_manager;
    $this->langcode = $language_manager->getCurrentLanguage()->getId();
    $this->dateTime = $date_time;
    $this->cache = $cache;
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
   * Method to provide the Language prefix for magento, per language.
   *
   * @return string
   *   The Language prefix for current language.
   */
  private function getMagentoLangPrefix() {
    // For current language, we access the config directly.
    if ($this->langcode == $this->languageManager->getDefaultLanguage()->getId()) {
      $config = $this->config;
    }
    // We get store id from translated config for other languages.
    else {
      $config = $this->languageManager->getLanguageConfigOverride($this->langcode, 'alshaya_api.settings');
    }

    return $config->get('magento_lang_prefix');
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

    if ($cache = $this->cache->get($cid)) {
      $this->token = $cache->data;
    }
    else {
      $endpoint = 'integration/admin/token';

      $data = [];
      $data['username'] = $this->config->get('username');
      $data['password'] = $this->config->get('password');

      $response = $this->invokeApi($endpoint, $data, 'POST', FALSE);
      $token = trim($response, '"');

      // We always get token wrapped in double quotes.
      // For any other case we get either an array of full html.
      if (strlen($token) !== strlen($response) - 2) {
        $this->logger->critical('Unable to get token from magento');
        throw new \Exception('Unable to get token from magento');
      }

      $this->token = $token;

      // Calculate the timestamp when we want the cache to expire.
      $expire = $this->dateTime->getRequestTime() + $this->config->get('token_cache_time');

      // Set the stock in cache.
      $this->cache->set($cid, $this->token, $expire);
    }

    return $this->token;
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
        return NULL;
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

    $stores = NULL;

    if ($response && is_string($response)) {
      $stores = json_decode($response, TRUE);
    }

    return $stores;
  }

  /**
   * Function to get all the enabled SKUs from the API.
   *
   * @param array|string $types
   *   The SKUs type to get from Magento (simple, configurable).
   *
   * @return array
   *   An array of SKUs indexed by type.
   *
   * @TODO: Create appropriate endpoint on conductor and move this to commerce.
   */
  public function getSkus($types = ['simple', 'configurable']) {
    $endpoint = 'products?';

    // Query parameters to get all enabled SKUs. We only want the SKUs.
    // No need to retrieve the other fields. We order the result by update date
    // so if any SKU is updated/created while we do the requests, it will be
    // returned in the latest results.
    $query = [
      'searchCriteria' => [
        'filterGroups' => [
          0 => [
            'filters' => [
              0 => [
                'field' => 'status',
                'value' => 1,
                'condition_type' => 'eq',
              ],
              1 => [
                'field' => 'type_id',
                'condition_type' => 'eq',
              ],
            ],
          ],
        ],
        'sortOrders' => [
          0 => [
            'field' => 'updated_at',
            'direction' => 'ASC',
          ],
        ],
        // @TODO: Make page size configurable. Arbitrary value for now.
        'pageSize' => 50,
      ],
      'fields' => 'items[sku]',
    ];

    $mskus = [];

    foreach ($types as $type) {
      $query['searchCriteria']['filterGroups'][0]['filters'][1]['value'] = $type;

      $page = 0;
      $continue = TRUE;
      $previous_page_skus = [];
      $skus = [];

      while ($continue) {
        $continue = FALSE;
        $page += 1;
        $query['searchCriteria']['currentPage'] = $page;

        $response = $this->invokeApi($endpoint . http_build_query($query), [], 'GET');

        if ($response && is_string($response)) {
          if ($decode_response = json_decode($response, TRUE)) {
            $current_page_skus = array_column($decode_response['items'], 'sku');

            $skus = array_merge($skus, $current_page_skus);

            // We don't have any way to know we reached the latest page as
            // Magento keep continue returning the same latest result. For this
            // we test if there is any SKU in current page which was already
            // present in previous page. If yes, then we reached the end of
            // the list.
            if (empty(array_diff($previous_page_skus, $current_page_skus))) {
              $continue = TRUE;
            }

            $previous_page_skus = $current_page_skus;
          }
        }
      }

      $mskus[$type] = $skus;
    }

    return $mskus;
  }

  /**
   * Function to sync all stores.
   *
   * @return bool
   *   Flag to specify if sync was successful or not.
   */
  public function syncStores() {
    $stored_synced = FALSE;

    $store_locator_ids = [];

    // Do API call to get stores for each language.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      // Get all stores for particular language.
      $stores = $this->getStores($langcode);

      if ($stores && is_array($stores) && !empty($stores['items'])) {
        // Loop through all the stores and add/edit/translate the store node.
        foreach ($stores['items'] as $store) {
          $this->storeUtility->updateStore($store, $langcode);

          // Store code will be unique for node/language.
          $store_locator_ids[] = $store['store_code'];

          // If we update even single store, we return TRUE.
          $stored_synced = TRUE;
        }
      }
    }

    // If there is at least one store id.
    if (!empty($store_locator_ids)) {
      // Get orphan store node ids.
      $orphan_store_nids = $this->storeUtility->getOrphanStores($store_locator_ids);
      // Delete orphan stores.
      $this->storeUtility->deleteStores($orphan_store_nids);
    }

    return $stored_synced;
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
    if (empty($stores)) {
      return [];
    }

    // Start sequence from 1.
    $index = 1;

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

    // Add sequence and proper delivery_time label and low stock text.
    foreach ($stores as $index => $store) {
      $stores[$index]['sequence'] = $index + 1;

      // Display sts label by default.
      $time = $store['sts_delivery_time_label'];

      // Display configured value for rnc if available.
      if ($store['rnc_available'] && $this->clickCollectSettings) {
        $time = $this->clickCollectSettings->get('click_collect_rnc');
      }

      $stores[$index]['delivery_time'] = $this->t('Collect from store in <em>@time</em>', ['@time' => $time]);
      $stores[$index]['low_stock_text'] = $store['low_stock'] ? $this->t('Low stock') : '';
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
    $sku = urlencode($sku);

    $endpoint = 'click-and-collect/stores/product/' . $sku . '/lat/' . $lat . '/lon/' . $lon;
    $response = $this->invokeApi($endpoint, [], 'GET');

    $stores = [];

    if ($response && is_string($response)) {
      $stores = json_decode($response, TRUE);
    }

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
    $endpoint = 'click-and-collect/stores/cart/' . $cart_id . '/lat/' . $lat . '/lon/' . $lon;
    $response = $this->invokeApi($endpoint, [], 'GET');

    $stores = [];

    if ($response && is_string($response)) {
      $stores = json_decode($response, TRUE);
    }

    return $stores;
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
    $response = $this->invokeApi($endpoint, [], 'GET');

    if ($response && is_string($response)) {
      $locations = json_decode($response, TRUE);

      if ($locations && is_array($locations)) {
        return $locations;
      }
    }

    return [];
  }

  /**
   * Function to get customer address form.
   *
   * @return array
   *   The Form array from API response OR empty array.
   */
  public function getCustomerAddressForm() {
    $country_code = strtoupper(_alshaya_custom_get_site_level_country_code());
    $endpoint = 'deliverymatrix/address-structure/country/' . $country_code;

    $response = $this->invokeApi($endpoint, [], 'GET');

    if ($response && is_string($response)) {
      $form = json_decode($response, TRUE);

      if ($form && is_array($form)) {
        return $form;
      }
    }

    return [];
  }

}
