<?php

namespace Drupal\alshaya_acm_customer;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_acm_customer\HelperTrait\Orders;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\Cache;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Orders Manager.
 *
 * @todo Move all code from utility file to here.
 * Target file alshaya_acm_customer.orders.inc.
 *
 * @package Drupal\alshaya_acm_customer
 */
class OrdersManager {

  use Orders;
  use StringTranslationTrait;

  /**
   * API Wrapper object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Orders config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Cache Backend service for orders.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Cache Backend service for orders_count.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $countCache;

  /**
   * Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Product Info Helper.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  protected $productInfoHelper;

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * OrdersManager constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for orders.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $count_cache
   *   Cache Backend service for orders_count.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info Helper.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Module Handler service.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache,
                              LanguageManagerInterface $language_manager,
                              LoggerChannelFactoryInterface $logger_factory,
                              CacheBackendInterface $count_cache,
                              ProductInfoHelper $product_info_helper,
                              RequestStack $request,
                              ModuleHandlerInterface $module_handler) {
    $this->apiWrapper = $api_wrapper;
    $this->config = $config_factory->get('alshaya_acm_customer.orders_config');
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->logger = $logger_factory->get('alshaya_acm_customer');
    $this->countCache = $count_cache;
    $this->configFactory = $config_factory;
    $this->productInfoHelper = $product_info_helper;
    $this->currentRequest = $request->getCurrentRequest();
    $this->moduleHandler = $module_handler;
  }

  /**
   * Helper function to clear orders related cache for a user/email.
   *
   * @param int $customer_id
   *   Email for which cache needs to be cleared.
   * @param int $uid
   *   User id for which cache needs to be cleared.
   */
  public function clearOrderCache(int $customer_id, $uid = 0) {
    // Clear user's order cache.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $this->cache->delete('orders_list_' . $langcode . '_' . $customer_id);
    }

    // Clear user's order count cache.
    $this->countCache->delete('orders_count_' . $customer_id);

    if ($uid) {
      // Invalidate the cache tag when order is placed to reflect on the
      // user's recent orders.
      Cache::invalidateTags(['user:' . $uid . ':orders']);
    }
  }

  /**
   * Reset stock cache and Drupal cache of products in last order.
   *
   * @param array|null $order
   *   Order if already available in calling function.
   */
  public function clearLastOrderRelatedProductsCache(array $order = NULL) {
    if (empty($order)) {
      $order = _alshaya_acm_checkout_get_last_order_from_session();
    }

    foreach ($order['items'] as $item) {
      if ($sku_entity = SKU::loadFromSku($item['sku'])) {
        $sku_entity->refreshStock();
      }
    }
  }

  /**
   * Apply conditions and get order status.
   *
   * @param array $order
   *   Item array.
   *
   * @return string
   *   Status of order, ensures string can be used directly as class too.
   */
  public function getOrderStatusDetails(array $order) {
    if (is_array($order['status'])) {
      return $order['status'];
    }

    $class = 'status-pending';

    if (in_array($order['status'], $this->getOrderStatusReturned())) {
      $class = 'status-returned';
    }
    elseif (in_array($order['status'], $this->getOrderStatusDelivered())) {
      $class = 'status-delivered';
    }

    return [
      'text' => $order['extension']['customer_status'],
      'class' => $class,
      'key' => $order['status'],
    ];
  }

  /**
   * Get the status codes for delivered.
   *
   * @return array
   *   Status codes array.
   */
  public function getOrderStatusDelivered() {
    static $status = [];

    if (empty($status)) {
      $status = explode(',', $this->config->get('order_status_delivered'));
    }

    return $status;
  }

  /**
   * Get the status codes for returned.
   *
   * @return array
   *   Status codes array.
   */
  public function getOrderStatusReturned() {
    static $status = [];

    if (empty($status)) {
      $status = explode(',', $this->config->get('order_status_returned'));
    }

    return $status;
  }

  /**
   * Get orders.
   *
   * Status of orders keep changing, we store it in cache for only sometime.
   * Time to store in cache is configurable.
   *
   * @param int $customer_id
   *   Customer Commerce ID.
   * @param int $page_size
   *   Page size for the order API.
   * @param string $search_key
   *   Key to look for in $_GET for searching.
   * @param string $filter_key
   *   Key to look for in $_GET for filtering.
   *
   * @return array
   *   Orders.
   */
  public function getOrders(int $customer_id, int $page_size = 3, $search_key = '', $filter_key = '') {
    try {
      $query = $this->getOrdersQuery('customer_id', $customer_id);

      // Query page size.
      $query['searchCriteria']['pageSize'] = $page_size;

      $endpoint = 'orders';
      $request_options = [
        'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_search'),
      ];

      // Prepare a cache id to store the data in static cache.
      $cid = implode('_', [
        'static',
        $customer_id,
        $page_size,
        $search_key,
        $filter_key,
      ]);
      $orders = &drupal_static($cid);

      if (empty($orders)) {
        if ($page_size > 0) {
          $result = $this->apiWrapper->invokeApi($endpoint, $query, 'GET', FALSE, $request_options);
          // Decode the json string to get the order item.
          $result = Json::decode($result);
        }
        else {
          $query['searchCriteria']['pageSize'] = 100;
          $result = $this->apiWrapper->invokeApiWithPageLimit($endpoint, $request_options, $query['searchCriteria']['pageSize'], [], $query);
        }

        $orders = $result['items'] ?? [];
        if ($orders) {
          foreach ($orders as $key => $order) {
            // Allow other modules to alter order details.
            $this->moduleHandler->alter('alshaya_acm_customer_order_details', $order);
            $orders[$key] = $this->cleanupOrder($order);
          }
          // Update the order count cache.
          if (isset($result['total_count'])) {
            $this->countCache->set('orders_count_' . $customer_id, $result['total_count']);
          }
        }

        // Sort them by default by date.
        usort($orders, fn($a, $b) => $b['created_at'] > $a['created_at']);

        // Update order items to have unique records only.
        // For configurable products we get it twice (once for parent and once
        // for selected variant).
        foreach ($orders as &$order) {
          $order_items = [];
          foreach ($order['items'] as $item) {
            // If product is virtual then use item_id instead of sku as key.
            if (!isset($order_items[$item['sku']]) && !$item['is_virtual']) {
              $order_items[$item['sku']] = $item;
            }
            else {
              $order_items[$item['item_id']] = $item;
            }
            $sku_entity = SKU::loadFromSku(alshaya_acm_customer_clean_sku($item['sku']));
            if ($sku_entity instanceof SKUInterface) {
              $order_items[$item['sku']]['name'] = $this->productInfoHelper->getTitle($sku_entity, 'basket');
            }

          }
          $order['items'] = $order_items;
        }
      }
    }
    catch (\Exception) {
      // Exception message is already added to log in APIWrapper.
      $orders = [];
    }

    $filtered_orders = $orders;
    // Search by Order ID, SKU, Name.
    $search = $search_key ? $this->currentRequest->query->get($search_key) : NULL;
    if ($search) {
      $filtered_orders = array_filter($orders, function ($order) use ($search) {
        $search = (string) $search;
        // Search by Order ID.
        if (stripos($order['increment_id'], $search) > -1) {
          return TRUE;
        }

        foreach ($order['items'] as $orderItem) {
          // Search by name.
          if (stripos($orderItem['name'], $search) > -1) {
            return TRUE;
          }
          // Search by SKU.
          elseif (stripos(alshaya_acm_customer_clean_sku($orderItem['sku']), alshaya_acm_customer_clean_sku($search)) > -1) {
            return TRUE;
          }
        }

        return FALSE;
      });
    }

    // Filter order by status.
    $filter = $filter_key ? $this->currentRequest->get($filter_key) : NULL;
    if ($filter) {
      $filtered_orders = array_filter($orders, function ($order, $orderId) use ($filter) {
        $status = alshaya_acm_customer_get_order_status($order);
        if ($status['text'] == $filter) {
          return TRUE;
        }

        return FALSE;
      }, ARRAY_FILTER_USE_BOTH);
    }

    return $filtered_orders;
  }

  /**
   * Get orders count.
   *
   * We need only count for some cases like GTM and count won't change like
   * orders so we store them permanently.
   *
   * @param int $customer_id
   *   Customer Commerce ID.
   *
   * @return int
   *   Orders count.
   */
  public function getOrdersCount(int $customer_id) {
    $cid = 'orders_count_' . $customer_id;

    if ($cache = $this->countCache->get($cid)) {
      return $cache->data;
    }

    $query = $this->getOrdersQuery('customer_id', $customer_id);
    $query['searchCriteria']['pageSize'] = 1;

    $request_options = [
      'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_search'),
    ];

    $response = $this->apiWrapper->invokeApi('orders', $query, 'GET', FALSE, $request_options);
    $result = $response ? json_decode($response, TRUE) : [];
    $count = $result['total_count'] ?? 0;
    $this->countCache->set($cid, $count);

    return $count;
  }

  /**
   * Helper function to get specific order.
   *
   * @param string $increment_id
   *   Increment ID to get order for.
   *
   * @return array
   *   Order array if found.
   */
  public function getOrderByIncrementId(string $increment_id) {
    $query = $this->getOrdersQuery('increment_id', $increment_id);

    $request_options = [
      'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_search'),
    ];
    // This cid is to store the data in static cache.
    $cid = implode('_', [__FUNCTION__, $increment_id]);
    $result = &drupal_static($cid);

    if (empty($result)) {
      $response = $this->apiWrapper->invokeApi('orders', $query, 'GET', FALSE, $request_options);
      $result = $response ? json_decode($response, TRUE) : [];
    }

    if (!empty($result)) {
      $count = $result['total_count'] ?? 0;
      if (empty($count)) {
        return [];
      }
    }

    $order = !empty($result['items']) ? reset($result['items']) : [];
    return $this->cleanupOrder($order);
  }

  /**
   * Helper function to get specific order.
   *
   * @param int $order_id
   *   Order ID to get order for.
   *
   * @return array
   *   Order array if found.
   */
  public function getOrder(int $order_id) {
    $query = $this->getOrdersQuery('entity_id', $order_id);

    $request_options = [
      'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_search'),
    ];

    $response = $this->apiWrapper->invokeApi('orders', $query, 'GET', FALSE, $request_options);
    if (empty($response)) {
      return NULL;
    }
    $result = json_decode($response, TRUE);
    $count = $result['total_count'] ?? 0;
    if (empty($count)) {
      return NULL;
    }

    $order = !empty($result['items']) ? reset($result['items']) : [];
    return $this->cleanupOrder($order);
  }

  /**
   * Helper function to get last order of the customer.
   *
   * @param int $customer_id
   *   Customer ID to get order for.
   *
   * @return array
   *   Order array if found.
   */
  public function getLastOrder(int $customer_id) {
    $query = $this->getOrdersQuery('customer_id', $customer_id);
    $query['searchCriteria']['pageSize'] = 1;

    $request_options = [
      'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_search'),
    ];

    $response = $this->apiWrapper->invokeApi('orders', $query, 'GET', FALSE, $request_options);
    $result = $response ? json_decode($response, TRUE) : [];
    $count = $result['total_count'] ?? 0;
    if (empty($count)) {
      return NULL;
    }

    $order = !empty($result['items']) ? reset($result['items']) : [];
    return $this->cleanupOrder($order);
  }

  /**
   * Gets the refund text for the payment method.
   *
   * @param string $payment_method_code
   *   The payment method code, eg. cashondelivery.
   *
   * @return string|null
   *   The refund text. If payment method is excluded, null is returned.
   */
  public function getRefundText(string $payment_method_code) {
    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');
    $excluded_payment_methods = $checkout_settings->get('refund_exclude_payment_methods');
    $excluded_payment_methods = array_filter($excluded_payment_methods);

    if (in_array($payment_method_code, $excluded_payment_methods)) {
      return NULL;
    }

    return $this->t('The refund for cancelled items will be made to your account within 14 working days if you have paid for your order.');
  }

  /**
   * Get orders count by Customer E-Mail.
   *
   * We need only count for some cases like GTM and count won't change like
   * orders so we store them permanently.
   *
   * @param string $customer_email
   *   Customer E-Mail.
   *
   * @return int
   *   Orders count.
   */
  public function getOrdersCountByCustomerMail(string $customer_email) {
    $request_options = [
      'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_search'),
    ];

    $query = $this->getOrdersQuery('customer_email', $customer_email);
    $query['searchCriteria']['pageSize'] = 1;
    $response = $this->apiWrapper->invokeApi('orders', $query, 'GET', FALSE, $request_options);

    $result = $response ? json_decode($response, TRUE) : [];

    return $result['total_count'] ?? 0;
  }

  /**
   * Helper function to get specific order by quote id.
   *
   * @param string $quote_id
   *   Quote ID to get order for.
   *
   * @return array
   *   Order array if found.
   */
  public function getOrderByQuoteId(string $quote_id) {
    $query = $this->getOrdersQuery('quote_id', $quote_id);

    $request_options = [
      'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_search'),
    ];

    $response = $this->apiWrapper->invokeApi('orders', $query, 'GET', FALSE, $request_options);
    if (empty($response)) {
      return NULL;
    }
    $result = json_decode($response, TRUE);
    $count = $result['total_count'] ?? 0;
    if (empty($count)) {
      return NULL;
    }

    $order = !empty($result['items']) ? reset($result['items']) : [];
    return $this->cleanupOrder($order);
  }

}
