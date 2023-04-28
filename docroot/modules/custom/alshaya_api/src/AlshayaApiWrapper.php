<?php

namespace Drupal\alshaya_api;

use Drupal\acq_commerce\Conductor\APIWrapper;
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
use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\TransferStats;
use Drupal\acq_commerce\I18nHelper;

/**
 * Class Alshaya Api Wrapper.
 */
class AlshayaApiWrapper {

  use StringTranslationTrait;

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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new AlshayaApiWrapper object.
   *
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
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    TimeInterface $date_time,
    CacheBackendInterface $cache,
    LoggerChannelFactoryInterface $logger_factory,
    I18nHelper $i18n_helper,
    StateInterface $state,
    FileSystemInterface $fileSystem,
    MagentoApiHelper $mdc_helper,
    ModuleHandlerInterface $module_handler,
    AccountInterface $current_user,
    ConfigFactoryInterface $config_factory
  ) {
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
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
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
    return $this->configFactory
      ->get('alshaya_api.settings')
      ->get('magento_lang_prefix')[$this->langcode];
  }

  /**
   * Wrapper function to get authenticated http client.
   *
   * @param string $url
   *   Base URL.
   * @param string $channel
   *   GET channel key i.e: assist-app, web etc.
   *
   * @return \GuzzleHttp\Client
   *   Client object.
   */
  private function getClient($url, $channel = NULL) {
    $config = $this->configFactory->get('alshaya_api.settings');
    $stack = HandlerStack::create();
    $middleware = new Oauth1([
      'consumer_key' => $config->get('consumer_key'),
      'consumer_secret' => $config->get('consumer_secret'),
      'token' => $config->get('access_token'),
      'token_secret' => $config->get('access_token_secret'),
      'signature_method' => $config->get('signature_method'),
    ]);
    $stack->push($middleware);

    // Set default header.
    $headers = [
      'Content-Type' => 'application/json',
    ];

    // Referring channel params and set header in case of assist app or web.
    if ($channel) {
      $headers += [
        'Alshaya-Channel' => $channel,
      ];
    }
    return new Client([
      'base_uri' => $url,
      'handler' => $stack,
      'auth' => 'oauth',
      'headers' => $headers,
    ]);
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
   * @param bool $throw_exception
   *   Flag to specifiy if exception should be thrown or handled.
   * @param array $options
   *   Options to send to the request.
   * @param string $channel
   *   GET channel key i.e: assist-app, web etc.
   *
   * @return mixed
   *   Response from the API.
   */
  public function invokeApi($endpoint, array $data = [], $method = 'POST', bool $throw_exception = FALSE, array $options = [], string $channel = NULL) {
    $settings = $this->configFactory->get('alshaya_api.settings');

    try {
      $url = $settings->get('magento_host');
      $url .= '/' . $this->getMagentoLangPrefix();
      $url .= '/' . $settings->get('magento_api_base');
      $client = $this->getClient($url, $channel);
      $url .= '/' . $endpoint;

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

      $that = $this;
      $options['on_stats'] = function (TransferStats $stats) use ($that) {
        $code = ($stats->hasResponse())
          ? $stats->getResponse()->getStatusCode()
          : 0;

        $that->logger->info(sprintf(
          'Finished API request %s in %.4f. Response code: %d. Method: %s. X-Cache: %s; X-Cache-Hits: %s; X-Served-By: %s;',
          $stats->getEffectiveUri(),
          $stats->getTransferTime(),
          $code,
          $stats->getRequest()->getMethod(),
          $stats->hasResponse() ? $stats->getResponse()->getHeaderLine('x-cache') : '',
          $stats->hasResponse() ? $stats->getResponse()->getHeaderLine('x-cache-hits') : '',
          $stats->hasResponse() ? $stats->getResponse()->getHeaderLine('x-served-by') : ''
        ));
      };

      $response = $client->request($method, $url, $options);
      $result = $response->getBody()->getContents();
      // Magento sends 401 response due to se error.
      if ($response->getStatusCode() == 401) {
        throw new \Exception('Magento send 401 response', 401);
      }
      // Magento actually down or fatal error.
      if ($response->getStatusCode() >= 500) {
        throw new \Exception('Back-end system is down', APIWrapper::API_DOWN_ERROR_CODE);
      }

      try {
        $json = Json::decode($result);
        if (is_array($json) && !empty($json['message']) && count($json) === 1) {
          // Let the code invoking this know if the response is 404.
          $code = $response->getStatusCode() == 404 ? 404 : 600;
          throw new \Exception($json['message'], $code);
        }
      }
      catch (\Exception $e) {
        // Let the outer catch handle logging of error and handling response.
        // We avoid other exceptions related to JSON parsing here.
        if (in_array($e->getCode(), [404, 600])) {
          throw $e;
        }
      }
    }
    catch (\Exception $e) {
      if ($throw_exception) {
        throw $e;
      }
      $result = NULL;

      $this->logger->error('Exception while invoking API @api. Message: @message.', [
        '@api' => $url,
        '@message' => $e->getMessage(),
      ]);
    }

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
      'group_id' => 0,
    ];

    $filters[] = [
      'field' => 'store_id',
      'value' => $this->i18nHelper->getStoreIdFromLangcode($langcode),
      'condition_type' => 'eq',
      'group_id' => 1,
    ];

    $page_size = 1000;

    // Filter page size.
    $filters['page_size'] = $page_size;

    $endpoint = 'storeLocator/search?';
    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('store_search'),
    ];

    return $this->invokeApiWithPageLimit($endpoint, $request_options, $page_size, $filters);
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
    $lang_prefix = explode('_', $this->getMagentoLangPrefix())[0];

    $path = file_create_url($this->fileSystem->realpath("temporary://"));
    $filename = 'merchandising-report-' . $lang_prefix . '.csv';

    $download_time = $this->state->get('alshaya_api.last_report_download');
    $max_age = Settings::get('merch_report_max_age', 3600);

    // We download a new merch report if asked, if too old or if file does not
    // exist yet in temporary directory.
    if ($reset || $download_time < (time() - $max_age) || !file_exists($path . '/' . $filename)) {
      $settings = $this->configFactory->get('alshaya_api.settings');
      $url = $settings->get('magento_host') . '/media/reports/merchandising/merchandising-report-' . $lang_prefix . '.csv';

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
  public function getEnabledSkusFromMerchandisingReport($types = [
    'simple',
    'configurable',
  ], $reset = TRUE) {
    $handle = $this->getMerchandisingReport($reset);

    $mskus = [];
    foreach ($types as $type) {
      $mskus[$type] = [];
    }

    // We have not been able to open the stream.
    if (!$handle) {
      // @todo Add some logs.
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
        if (!in_array(trim(strtolower($data[$indexes['type']])), [
          'simple product',
          'configurable product',
        ])) {
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
  public function getSkus(array $types = [
    'simple',
    'configurable',
  ], array $skus = []) {
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
        // @todo Make page size configurable. Arbitrary value for now.
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

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('cnc_check'),
    ];

    $response = $this->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

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

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('cnc_check'),
    ];

    $response = $this->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

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

    $page_size = 1000;

    // Filter page size.
    $filters['page_size'] = $page_size;

    $endpoint = 'deliverymatrix/address-locations/search?';
    return $this->invokeApiWithPageLimit($endpoint, [], $page_size, $filters);
  }

  /**
   * Function to invoke APIs with page limit.
   *
   * @param string $endpoint
   *   The API endpoint.
   * @param array $request_options
   *   Request Options.
   * @param int $page_size
   *   Page size/limit of the API.
   * @param array $filters
   *   Filters array.
   * @param array $data
   *   Data to send to API.
   *
   * @return array
   *   Array of items from API.
   */
  public function invokeApiWithPageLimit(string $endpoint, array $request_options, int $page_size, array $filters = [], array $data = []) {
    $response_array = [];
    $current_page = 1;
    $all_items = [];

    do {
      // If filters are present then prepare filter url.
      if ($filters) {
        $filters['current_page'] = $current_page++;
        $endpoint .= $this->prepareFilterUrl($filters);
      }

      // If data is present, add current page number to it.
      if ($data) {
        $data['searchCriteria']['current_page'] = $current_page++;
      }

      $response = $this->invokeApi($endpoint, $data, 'GET', FALSE, $request_options);

      if ($response && is_string($response)) {
        $response_array = json_decode($response, TRUE);

        // Merging response items from all the pages of the API call.
        if ($response_array && is_array($response_array) && !empty($response_array['items'])) {
          $all_items['items'] = array_merge($all_items['items'] ?? [], $response_array['items']);
        }
      }
      $no_of_pages ??= ceil($response_array['total_count'] / $page_size);

    } while ($current_page <= $no_of_pages);

    return $all_items;
  }

  /**
   * Function to get customer address form.
   *
   * @return array
   *   The Form array from API response OR empty array.
   */
  public function getCustomerAddressForm() {
    $country_code = strtoupper(_alshaya_custom_get_site_level_country_code());
    return $this->getCustomerAddressFormByCountryCode($country_code);
  }

  /**
   * Get customer address form by country code.
   *
   * @param string $country_code
   *   Country code.
   *
   * @return array
   *   The Form array from API response OR empty array.
   */
  public function getCustomerAddressFormByCountryCode($country_code) {
    $endpoint = 'deliverymatrix/address-structure/country/' . $country_code;

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('dm_structure_get'),
    ];

    $response = $this->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

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
      if (!is_array($filter)) {
        $url .= $base . '[' . $index . ']=' . $filter . '&';
        continue;
      }

      $filter_group_id = $filter['group_id'] ?? $group_id;

      foreach ($filter as $key => $value) {
        if ($key === 'group_id') {
          continue;
        }

        // Prepared string like below.
        // searchCriteria[filter_groups][0][filters][0][field]=field
        // This is how Magento search criteria in APIs work.
        $url .= $base . '[filter_groups][' . $filter_group_id . '][filters][' . $index . '][' . $key . ']=' . $value;

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

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('cart_selected_payment'),
    ];

    $response = $this->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

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

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('stock_get'),
    ];

    $response = $this->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

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

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('product_get'),
    ];

    $response = $this->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

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
   * Get Cart by invoking Magento API directly.
   *
   * @param string $cart_id
   *   Cart ID.
   *
   * @return array
   *   API Response.
   */
  public function getCart(string $cart_id) {
    $endpoint = sprintf('carts/%d/getCart', $cart_id);

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('cart_get'),
    ];

    return $this->invokeApi($endpoint, [], 'GET', TRUE, $request_options);
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
    $endpoint = sprintf('carts/%d/updateCart', $cart_id);

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('cart_update'),
    ];

    return $this->invokeApi($endpoint, $cart, 'JSON', FALSE, $request_options);
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
   * Get the customer token.
   *
   * @param string $mail
   *   The mail address.
   * @param string $pass
   *   The customer password.
   *
   * @return array
   *   The customer data with token.
   */
  public function getCustomerUsingAuthDetails(string $mail, string $pass) {
    $token = NULL;
    $customer = [];
    $endpoint = 'integration/customer/token';

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('customer_authenticate'),
    ];

    try {
      $token = $this->invokeApi(
        $endpoint,
        [
          'username' => $mail,
          'password' => $pass,
        ],
        'JSON',
        FALSE,
        $request_options
      );

      $token = json_decode($token, NULL);
      // If token could not be decoded, store NULL.
      $token = $token === FALSE ? NULL : $token;
    }
    catch (\Exception $e) {
      $this->logger->notice('Exception while getting customer token. Error: @response. E-mail: @email', [
        '@response' => $e->getMessage(),
        '@email' => $mail,
      ]);
    }

    try {
      // Get the user data from Magento.
      $customer = !empty($token) ? $this->getCustomer($mail) : $customer;
    }
    catch (\Exception $e) {
      $this->logger->notice('Exception while getting customer data. Error: @response. E-mail: @email', [
        '@response' => $e->getMessage(),
        '@email' => $mail,
      ]);
    }

    $customer['token'] = $token;
    return $customer;
  }

  /**
   * Authenticate customer through magento api using email.
   *
   * @param string $mail
   *   The mail address.
   *
   * @return string
   *   The customer token OR null.
   */
  public function getCustomerTokenBySocialDetail(string $mail) {
    $endpoint = 'integration/customer/token/bySocialDetail';

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('customer_authenticate'),
    ];

    try {
      return $this->invokeApi(
        $endpoint,
        [
          'customerEmail' => $mail,
        ],
        'JSON',
        TRUE,
        $request_options
      );
    }
    catch (\Exception $e) {
      $this->logger->error('Exception while authenticating customer data @data against the api @api. Message: @message.', [
        '@data' => $mail,
        '@api' => $endpoint,
        '@message' => $e->getMessage(),
      ]);

      if ($e->getCode() === 401 && $this->currentUser->isAuthenticated()) {
        user_logout();

        // We redirect to an user/login path.
        $response = new LocalRedirectResponse(Url::fromRoute('user.login')->toString());
        $response->send();
        return $response;
      }

      return NULL;
    }
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
    $endpoint = 'customers/search';
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

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('customer_search'),
    ];

    try {
      $response = $this->invokeApi($endpoint, $query_string_array, 'GET', TRUE, $request_options);
      $result = Json::decode($response);
      if (!empty($result['items'])) {
        $customer = MagentoApiResponseHelper::customerFromSearchResult(reset($result['items']));
        $customer = $this->mdcHelper->cleanCustomerData($customer);
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Exception while fetching customer data @data against the api @api. Message: @message.', [
        '@data' => $email,
        '@api' => $endpoint,
        '@message' => $e->getMessage(),
      ]);

      if ($e->getCode() === 401 && $this->currentUser->isAuthenticated()) {
        user_logout();

        // We redirect to an user/login path.
        $response = new LocalRedirectResponse(Url::fromRoute('user.login')->toString());
        $response->send();
        return $response;
      }

      return NULL;
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
    $opt = [];
    $endpoint = 'customers';

    $opt['json']['customer'] = $customer;

    // Invoke the alter hook to allow all modules to update the customer data.
    $this->moduleHandler->alter('alshaya_api_update_customer_api_request', $opt);

    // Do some cleanup.
    $opt['json']['customer'] = $this->mdcHelper->cleanCustomerData($opt['json']['customer']);
    $opt['json']['customer'] = MagentoApiRequestHelper::prepareCustomerDataForApi($opt['json']['customer']);

    $request_options = [];
    $method = 'JSON';
    if (!empty($opt['json']['customer']['id'])) {
      $endpoint .= '/' . $opt['json']['customer']['id'];
      $method = 'PUT';
      $request_options = [
        'timeout' => $this->mdcHelper->getPhpTimeout('customer_update'),
      ];
    }
    else {
      $request_options = [
        'timeout' => $this->mdcHelper->getPhpTimeout('customer_create'),
      ];
    }

    try {
      // Log the data we are posting to magento.
      $logger_data = $opt['json'];
      if (!empty($logger_data['password'])) {
        // Mask password in log.
        $logger_data['password'] = 'XXXXXXXXXXXX';
      }
      $this->logger->notice('Updating customer on Magento from Drupal. Data: @data Method: @method Endpoint: @endpoint', [
        '@data' => json_encode($logger_data),
        '@method' => $method,
        '@endpoint' => $endpoint,
      ]);

      $response = $this->invokeApi($endpoint, $opt['json'], $method, FALSE, $request_options);
      $response = Json::decode($response);
      if (!empty($response)) {
        // Move the cart_id into the customer object.
        if (isset($response['cart_id'])) {
          $response['custom_attributes'][] = [
            'attribute_code' => 'cart_id',
            'value' => $response['cart_id'],
          ];
        }

        // In few scenarios like missing required field, MDC returns error as
        // an array but we don't need to process response if its for an error.
        if (is_array($response) && empty($response['message'])) {
          $response = MagentoApiResponseHelper::customerFromSearchResult($response);
        }
        else {
          // If we reach here, it means we get the response from MDC which
          // is not as per required format/array. So we pass that info to
          // the exception so this can be logged.
          $log_string = is_string($response) ? $response : json_encode($response);
          throw new \Exception($log_string);
        }
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
  public function updateCustomerPass(array $customer, $password) {
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

    $endpoint = sprintf('customers/%d/set-password', $cid);

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('customer_password_set'),
    ];

    try {
      $response = $this->invokeApi(
        $endpoint,
        [
          'customer_id' => $cid,
          'password' => $password,
        ],
        'JSON',
        FALSE,
        $request_options
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

  /**
   * Function to get all the categories from Commerce system.
   *
   * @param string $langcode
   *   Language code.
   *
   * @return mixed
   *   Stores array.
   */
  public function getCategories($langcode) {
    $this->updateStoreContext($langcode);

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('get_categories'),
    ];
    $response = $this->invokeApi(
      'categories/extended',
      [],
      'GET',
      FALSE,
      $request_options
    );

    $categories = NULL;
    if ($response && is_string($response)) {
      $categories = json_decode($response, TRUE);
    }

    return $categories;
  }

  /**
   * Wrapper function to decrypt smart agent data.
   *
   * @param string $data
   *   Encrypted data.
   * @param string $channel
   *   GET channel key i.e: assist-app, web etc.
   *
   * @return array
   *   Decrypted processed data.
   */
  public function getDecryptedSmartAgentData(string $data, string $channel = NULL) {
    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('smart_agent_resume'),
    ];

    $response = $this->invokeApi(
      'smart-agent',
      ['data' => $data],
      'JSON',
      FALSE,
      $request_options,
      $channel
    );

    if ($response && is_string($response)) {
      return json_decode($response, TRUE);
    }

    return $response;
  }

  /**
   * Wrapper function to associate cart to customer.
   *
   * @param string|int $cart_id
   *   Cart ID.
   * @param string|int $customer_id
   *   Customer ID.
   *
   * @return mixed
   *   API response or false if API called.
   */
  public function associateCartToCustomer($cart_id, $customer_id) {
    $url = sprintf('carts/%d/associate-cart', $cart_id);

    try {
      $data = [
        'customerId' => $customer_id,
        'cartId' => $cart_id,
      ];

      $request_options = [
        'timeout' => $this->mdcHelper->getPhpTimeout('cart_associate'),
      ];

      return $this->invokeApi($url, $data, 'JSON', TRUE, $request_options);
    }
    catch (\Exception $e) {
      $this->logger->error('Error while associating cart to customer. CartID: @cart_id, CustomerID: @customer_id, Error: @message.', [
        '@cart_id' => $cart_id,
        '@customer_id' => $customer_id,
        '@message' => $e->getMessage(),
      ]);
    }

    return FALSE;
  }

  /**
   * Returns the magento API helper service.
   *
   * @return \Drupal\alshaya_api\Helper\MagentoApiHelper
   *   The magento API helper service.
   */
  public function getMagentoApiHelper() {
    return $this->mdcHelper;
  }

  /**
   * Function to subscribe an email for newsletter.
   *
   * @param string $email
   *   E-Mail to subscribe.
   *
   * @return array
   *   Array containing status of subscription.
   */
  public function subscribeNewsletter(string $email) {
    try {
      $request_options = [
        'timeout' => $this->mdcHelper->getPhpTimeout('subscribe_newsletter'),
      ];

      $status = $this->invokeApi('newsletter/subscribe', ['email' => $email], 'JSON', TRUE, $request_options);
      return json_decode($status, TRUE);
    }
    catch (\Exception) {
      $this->logger->error('Error while calling newsletter subscribe API.');
      return ['status' => 0];
    }
  }

}
