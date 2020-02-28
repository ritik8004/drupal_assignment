<?php

namespace Drupal\alshaya_api;

use Drupal\acq_commerce\I18nHelper;
use Drupal\alshaya_api\Helper\MagentoApiHelper;
use Drupal\alshaya_api\Helper\MagentoApiRequestHelper;
use Drupal\alshaya_api\Helper\MagentoApiResponseHelper;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use springimport\magento2\apiv1\ApiFactory;
use springimport\magento2\apiv1\Configuration;

/**
 * Class AlshayaApiWrapper.
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
   * The LoggerFactory object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * The state factory.
   *
   * @var \Drupal\Core\KeyValueStore\StateInterface
   */
  protected $state;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

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
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state factory.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The filesystem service.
   * @param \Drupal\alshaya_api\Helper\MagentoApiHelper $mdc_helper
   *   The magento api helper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    TimeInterface $date_time,
    CacheBackendInterface $cache,
    LoggerChannelFactoryInterface $logger_factory,
    I18nHelper $i18n_helper,
    StateInterface $state,
    FileSystemInterface $fileSystem,
    MagentoApiHelper $mdc_helper,
    ModuleHandlerInterface $module_handler
  ) {
    $this->config = $config_factory->get('alshaya_api.settings');
    $this->languageManager = $language_manager;
    $this->langcode = $language_manager->getCurrentLanguage()->getId();
    $this->dateTime = $date_time;
    $this->cache = $cache;
    $this->logger = $logger_factory->get('alshaya_api');
    $this->i18nHelper = $i18n_helper;
    $this->state = $state;
    $this->fileSystem = $fileSystem;
    $this->mdcHelper = $mdc_helper;
    $this->moduleHandler = $module_handler;
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
   * Function to reset context langcode for API calls.
   */
  public function resetStoreContext() {
    $this->langcode = $this->languageManager->getCurrentLanguage()->getId();
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
   *
   * @throws \Exception
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

      $response = $this->invokeApiWithToken($endpoint, $data, 'POST', FALSE);
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

      $this->cache->set($cid, $this->token, $expire);
    }

    return $this->token;
  }

  /**
   * Wrapper function to get authenticated http client.
   *
   * @param string $url
   *   Base URL.
   *
   * @return \GuzzleHttp\Client
   *   Client object.
   */
  private function getClient($url) {
    $configuration = new Configuration();
    $configuration->setBaseUri($url);
    $configuration->setConsumerKey($this->config->get('consumer_key'));
    $configuration->setConsumerSecret($this->config->get('consumer_secret'));
    $configuration->setToken($this->config->get('access_token'));
    $configuration->setTokenSecret($this->config->get('access_token_secret'));

    return (new ApiFactory($configuration))->getApiClient();
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
   *
   * @return mixed
   *   Response from the API.
   */
  public function invokeApi($endpoint, array $data = [], $method = 'POST') {
    $consumer_key = $this->config->get('consumer_key');
    if (empty($consumer_key)) {
      return $this->invokeApiWithToken($endpoint, $data, $method, TRUE);
    }

    try {
      $url = $this->config->get('magento_host');
      $url .= '/' . $this->getMagentoLangPrefix();
      $url .= '/' . $this->config->get('magento_api_base');

      $client = $this->getClient($url);
      $url .= '/' . $endpoint;

      $options = [];
      if ($method == 'POST') {
        $options['form_params'] = $data;
      }
      elseif ($method == 'JSON') {
        $options['json'] = $data;
        $method = 'POST';
      }
      elseif ($method == 'GET' && !empty($data)) {
        $options['query'] = $data;
      }
      elseif ($method == 'PUT') {
        $options['json'] = $data;
      }

      $response = $client->request($method, $url, $options);
      $result = $response->getBody()->getContents();

      try {
        $json = Json::decode($result);
        if (is_array($json) && !empty($json['message']) && count($json) === 1) {
          throw new \Exception($json['message'], 600);
        }
      }
      catch (\Exception $e) {
        // Let the outer catch handle logging of error and handling response.
        // We avoid other exceptions related to JSON parsing here.
        if ($e->getCode() === 600) {
          throw $e;
        }
      }
    }
    catch (\Exception $e) {
      $result = NULL;
      $this->logger->error('Exception while invoking API @api. Message: @message.', [
        '@api' => $url,
        '@message' => $e->getMessage(),
      ]);
    }

    return $result;
  }

  /**
   * Function to invoke the API using user/password based token.
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
  public function invokeApiWithToken($endpoint, array $data = [], $method = 'POST', $requires_token = TRUE) {
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

    $filters = [];

    // Always add status check.
    $filters[] = [
      'field' => 'status',
      'value' => '1',
      'condition_type' => 'eq',
    ];

    $filters[] = [
      'field' => 'store_id',
      'value' => $this->i18nHelper->getStoreIdFromLangcode($langcode),
      'condition_type' => 'eq',
    ];

    $endpoint = 'storeLocator/search?';
    $endpoint .= $this->prepareFilterUrl($filters);

    $response = $this->invokeApi($endpoint, [], 'GET');

    $stores = NULL;

    if ($response && is_string($response)) {
      $stores = json_decode($response, TRUE);
    }

    return $stores;
  }

  /**
   * Function to get the merchandising report from Magento.
   *
   * @param bool $reset
   *   Either to force download of a fresh merch report.
   *
   * @return bool|resource
   *   The file opened or FALSE if not accessible.
   */
  public function getMerchandisingReport($reset = TRUE) {
    $lang_prefix = explode('_', $this->config->get('magento_lang_prefix'))[0];

    $path = file_create_url($this->fileSystem->realpath("temporary://"));
    $filename = 'merchandising-report-' . $lang_prefix . '.csv';

    $download_time = $this->state->get('alshaya_api.last_report_download');
    $max_age = $this->config->get('merch_report_max_age') ?? 3600;

    // We download a new merch report if asked, if too old or if file does not
    // exist yet in temporary directory.
    if ($reset || $download_time < (time() - $max_age) || !file_exists($path . '/' . $filename)) {
      $url = $this->config->get('magento_host') . '/media/reports/merchandising/merchandising-report-' . $lang_prefix . '.csv';

      // We need this to avoid issue with invalid certificate.
      $context = [
        'ssl' => [
          'verify_peer' => FALSE,
          'verify_peer_name' => FALSE,
        ],
      ];
      $handle = @fopen($url, 'r', FALSE, stream_context_create($context));

      $fp = fopen($path . '/' . $filename, 'w');
      while (($data = fgets($handle)) !== FALSE) {
        fwrite($fp, $data);
      }
      fclose($fp);

      $this->state->set('alshaya_api.last_report_download', time());
    }

    return @fopen($path . '/' . $filename, 'r');
  }

  /**
   * Function to get all the enabled SKUs from the Merchandising Report.
   *
   * @param array|string $types
   *   The SKUs type to get from Magento (simple, configurable).
   * @param bool $reset
   *   Force download of a fresh merch report.
   *
   * @return array
   *   An array of SKUs indexed by type.
   */
  public function getEnabledSkusFromMerchandisingReport($types = ['simple', 'configurable'], $reset = TRUE) {
    $handle = $this->getMerchandisingReport($reset);

    $mskus = [];
    foreach ($types as $type) {
      $mskus[$type] = [];
    }

    // We have not been able to open the stream.
    if (!$handle) {
      // @TODO: Add some logs.
      return $mskus;
    }

    // Because the column position may vary across brands, we are browsing the
    // report's first line to identify the position of each column we need.
    $indexes = [
      'partnum' => FALSE,
      'status' => FALSE,
      'visibility' => FALSE,
      'type' => FALSE,
      'price' => FALSE,
      'special_price' => FALSE,
      'web_qty' => FALSE,
    ];

    if ($data = fgetcsv($handle, 10000, ',')) {
      foreach ($data as $position => $key) {
        foreach ($indexes as $name => $index) {
          if (trim(strtolower($key)) == $name) {
            $indexes[$name] = $position;
            continue;
          }
        }
      }

      if (in_array(FALSE, $indexes)) {
        return $mskus;
      }

      while (($data = fgetcsv($handle, 10000, ',')) !== FALSE) {
        // We don't deal with SKUs which we don't have enough information.
        if (!isset($data[$indexes['status']]) || !isset($data[$indexes['type']])) {
          continue;
        }

        // We don't deal with disabled SKUs.
        if (trim(strtolower($data[$indexes['status']])) !== 'enabled') {
          continue;
        }

        // This is a weird case where not visible SKU does not have any related
        // configurable.
        if (empty($data[$indexes['type']]) && trim(strtolower($data[$indexes['visibility']])) == 'not visible individually') {
          continue;
        }

        // We only deal with simple and configurable products.
        if (!in_array(trim(strtolower($data[$indexes['type']])), ['simple product', 'configurable product'])) {
          continue;
        }

        $type = trim(strtolower($data[$indexes['type']])) == 'simple product' ? 'simple' : 'configurable';

        // We filter the types we don't want to get.
        if (!in_array($type, $types)) {
          continue;
        }

        $mskus[$type][$data[$indexes['partnum']]] = [
          'sku' => $data[$indexes['partnum']],
          'price' => $data[$indexes['price']],
          'special_price' => $data[$indexes['special_price']],
          'qty' => (int) $data[$indexes['web_qty']],
        ];
      }
    }
    fclose($handle);

    return $mskus;
  }

  /**
   * Function to get enabled SKUs from the API.
   *
   * @param array $types
   *   The SKUs type to get from Magento (simple, configurable).
   * @param array $skus
   *   The SKUs to get from Magento.
   *
   * @return array
   *   An array of SKUs indexed by type.
   */
  public function getSkus(array $types = ['simple', 'configurable'], array $skus = []) {
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
            ],
          ],
          1 => [
            'filters' => [
              0 => [
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

    foreach ($skus as $index => $sku) {
      $query['searchCriteria']['filterGroups'][2]['filters'][$index] = [
        'field' => 'sku',
        'condition_type' => 'eq',
        'value' => $sku,
      ];
    }

    $mskus = [];
    foreach ($types as $type) {
      $mskus[$type] = [];
    }

    foreach ($types as $type) {
      $query['searchCriteria']['filterGroups'][1]['filters'][0]['value'] = $type;

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
            if (!empty($decode_response['items'])) {
              $current_page_skus = array_column($decode_response['items'], 'sku');

              $skus = array_unique(array_merge($skus, $current_page_skus));

              // We don't have any way to know we reached the latest page as
              // Magento keep continue returning the same latest result. For
              // this we test if there is any SKU in current page which was
              // already present in previous page. If yes, then we reached the
              // end of the list.
              if (!empty(array_diff($current_page_skus, $previous_page_skus))) {
                $continue = TRUE;
              }

              $previous_page_skus = $current_page_skus;
            }
          }
        }
      }

      if (!empty($skus)) {
        foreach ($skus as $sku) {
          $mskus[$type][$sku] = [
            'sku' => $sku,
            'price' => 0,
            'special_price' => 0,
            'qty' => 0,
          ];
        }
      }
    }

    return $mskus;
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
    $filters = [];

    $filters[] = [
      'field' => $filterField,
      'value' => $filterValue,
      'condition_type' => 'eq',
    ];

    // Always add status check.
    $filters[] = [
      'field' => 'status',
      'value' => '1',
      'condition_type' => 'eq',
    ];

    // Filter by Country.
    $filters[] = [
      'field' => 'country_id',
      'value' => strtoupper(_alshaya_custom_get_site_level_country_code()),
      'condition_type' => 'eq',
    ];

    $endpoint = 'deliverymatrix/address-locations/search?';
    $endpoint .= $this->prepareFilterUrl($filters);
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

  /**
   * Function to get attribute info.
   *
   * @param string $attribute_code
   *   Attribute code.
   *
   * @return mixed
   *   API Response.
   */
  public function getProductAttributeWithSwatches($attribute_code) {
    $endpoint = 'products/attributes-with-swatches/' . $attribute_code;
    return $this->invokeApi($endpoint, [], 'GET');
  }

  /**
   * Helper function to prepare filter url query string.
   *
   * @param array $filters
   *   Array containing all filters, must contain field and value, can contain
   *   condition_type too or all that is supported by Magento.
   * @param string $base
   *   Filter Base, mostly searchCriteria.
   * @param int $group_id
   *   Filter group id, mostly 0.
   *
   * @return string
   *   Prepared URL query string.
   */
  private function prepareFilterUrl(array $filters, $base = 'searchCriteria', $group_id = 0) {
    $url = '';

    foreach ($filters as $index => $filter) {
      foreach ($filter as $key => $value) {
        // Prepared string like below.
        // searchCriteria[filter_groups][0][filters][0][field]=field
        // This is how Magento search criteria in APIs work.
        $url .= $base . '[filter_groups][' . $group_id . '][filters][' . $index . '][' . $key . ']=' . $value;

        // Add query params separator.
        $url .= '&';
      }
    }

    return $url;
  }

  /**
   * Get selected payment method for a cart.
   *
   * @param int $cart_id
   *   Cart ID.
   *
   * @return string
   *   Selected payment method code.
   */
  public function getCartPaymentMethod(int $cart_id) {
    $endpoint = 'carts/' . $cart_id . '/selected-payment-method';
    $response = $this->invokeApi($endpoint, [], 'GET');

    if (empty($response)) {
      return '';
    }

    $response = json_decode($response, TRUE);
    return $response['method'] ?? '';
  }

  /**
   * Get the stock for a sku.
   *
   * @param string $sku
   *   The sku to fetch the stock for.
   *
   * @return int
   *   The returned stock for the sku.
   */
  public function getStock(string $sku) : int {
    $endpoint = 'stockItems/' . urlencode($sku);
    $response = $this->invokeApi($endpoint, [], 'GET');

    if (empty($response)) {
      return 0;
    }

    $response = json_decode($response, TRUE);
    return $response['qty'] ?? 0;
  }

  /**
   * Get data for a sku.
   *
   * @param string $sku
   *   The sku to fetch the data for.
   *
   * @return array
   *   The sku object from Magento.
   */
  public function getSku(string $sku) : array {
    $endpoint = 'products/' . urlencode($sku);
    $response = $this->invokeApi($endpoint, [], 'GET');

    $response = json_decode($response, TRUE);

    return (!empty($response) && isset($response['message'])) ? [] : $response;
  }

  /**
   * Get data for all enabled SKUs from Magento.
   *
   * @return array
   *   The SKUs data.
   */
  public function getSkusData() : array {
    $endpoint = 'sanity-check-data';
    $response = $this->invokeApi($endpoint, [], 'GET');
    $response = json_decode($response, TRUE) ?? [];

    $skus = [];
    foreach ($response as $data) {
      $skus[$data['sku']] = $data;
    }

    return $skus;
  }

  /**
   * Update Cart by invoking Magento API directly.
   *
   * @param string $cart_id
   *   Cart ID.
   * @param array $cart
   *   Cart data.
   *
   * @return mixed
   *   API Response.
   */
  public function updateCart(string $cart_id, array $cart) {
    $endpoint = 'carts/' . $cart_id . '/updateCart';
    return $this->invokeApi($endpoint, $cart, 'JSON');
  }

  /**
   * Cancel cart reservation.
   *
   * @param string $cart_id
   *   Cart ID.
   * @param string $message
   *   Message to log.
   *
   * @return array
   *   API response.
   */
  public function cancelCartReservation(string $cart_id, string $message) {
    $endpoint = 'cancel/reserve/cart';
    $data = [
      'quoteId' => $cart_id,
      'message' => $message,
    ];

    $response = $this->invokeApi($endpoint, $data, 'JSON');

    return Json::decode($response);
  }

  /**
   * Authenticate customer through magento api.
   *
   * @param string $mail
   *   The mail address.
   * @param string $pass
   *   The customer password.
   *
   * @return array
   *   The array customer data OR empty array.
   */
  public function authenticateCustomerOnMagento(string $mail, string $pass) {
    $endpoint = 'customers/by-login-and-password';

    try {
      $response = $this->invokeApi(
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
      $response = Json::decode($response);
      // Move the cart_id into the customer object.
      if (isset($response['cart_id'])) {
        $response['customer']['custom_attributes'][] = [
          'attribute_code' => 'cart_id',
          'value' => $response['cart_id'],
        ];
      }
      return MagentoApiResponseHelper::customerFromSearchResult($response['customer']);
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
      $response = $this->invokeApi("customers/search", $query_string_array, 'GET');
      $result = Json::decode($response);
      if (!empty($result['items'])) {
        $customer = MagentoApiResponseHelper::customerFromSearchResult(reset($result['items']));
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
   *
   * @throws \Exception
   */
  public function updateCustomer(array $customer, array $options = []) {
    $endpoint = 'customers';

    $opt['json']['customer'] = $customer;

    if (isset($options['password']) && !empty($options['password'])) {
      $opt['json']['password'] = $options['password'];
    }

    // Invoke the alter hook to allow all modules to update the customer data.
    $this->moduleHandler->alter('alshaya_api_update_customer_api_request', $opt);

    // Do some cleanup.
    $opt['json']['customer'] = $this->mdcHelper->cleanCustomerData($opt['json']['customer']);
    $opt['json']['customer'] = MagentoApiRequestHelper::prepareCustomerDataForApi($opt['json']['customer']);

    $method = 'JSON';
    if (!empty($opt['json']['customer']['id'])) {
      $endpoint .= '/' . $opt['json']['customer']['id'];
      $method = 'PUT';
    }

    try {
      $response = $this->invokeApi($endpoint, $opt['json'], $method);
      $response = Json::decode($response);
      if (!empty($response)) {
        // Move the cart_id into the customer object.
        if (isset($response['cart_id'])) {
          $response['custom_attributes'][] = [
            'attribute_code' => 'cart_id',
            'value' => $response['cart_id'],
          ];
        }
        $response = MagentoApiResponseHelper::customerFromSearchResult($response);
      }

      // Update password api.
      if (!empty($response) && !empty($options['password'])) {
        try {
          $this->updateCustomerPass($response, $options['password']);
        }
        catch (\Exception $e) {
          throw $e;
        }
      }
    }
    catch (\Exception $e) {
      $response = NULL;
      $this->logger->error('Exception while invoking method @method for API @api. Message: @message.', [
        '@method' => __METHOD__,
        '@api' => $endpoint,
        '@message' => $e->getMessage(),
      ]);
    }

    return $response;
  }

  /**
   * Update customer password.
   *
   * @param array $customer
   *   The customer array.
   * @param string $password
   *   The password to update for customer.
   *
   * @return array|null
   *   Return array response.
   *
   * @throws \Exception
   */
  protected function updateCustomerPass(array $customer, $password) {
    $endpoint = 'customers/%d/set-password?';

    $cid = (int) $customer['customer_id'];
    $password = (string) $password;

    if ($cid < 1) {
      throw new \Exception(
        'updateCustomerPass: Missing customer id.'
      );
    }
    if (!strlen($password)) {
      throw new \Exception(
        'updateCustomerPass: Missing customer password.'
      );
    }

    $endpoint = sprintf($endpoint, $cid);
    $endpoint .= 'password=' . urlencode($password);

    try {
      $response = $this->invokeApi(
        $endpoint,
        [
          'customer_id' => $cid,
          'password' => $password,
        ],
        'JSON'
      );
      $response = Json::decode($response);
    }
    catch (\Exception $e) {
      $response = NULL;
      $this->logger->error('Exception while invoking method @method for API @api. Message: @message.', [
        '@method' => __METHOD__,
        '@api' => $endpoint,
        '@message' => $e->getMessage(),
      ]);
    }

    return $response;
  }

}
