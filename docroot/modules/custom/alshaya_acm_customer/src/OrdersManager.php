<?php

namespace Drupal\alshaya_acm_customer;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_customer\HelperTrait\Orders;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
   */
  public function __construct(AlshayaApiWrapper $api_wrapper,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache,
                              LanguageManagerInterface $language_manager,
                              LoggerChannelFactoryInterface $logger_factory,
                              CacheBackendInterface $count_cache) {
    $this->apiWrapper = $api_wrapper;
    $this->config = $config_factory->get('alshaya_acm_customer.orders_config');
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->logger = $logger_factory->get('alshaya_acm_customer');
    $this->countCache = $count_cache;
    $this->configFactory = $config_factory;
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
   */
  public function clearLastOrderRelatedProductsCache() {
    $order = _alshaya_acm_checkout_get_last_order_from_session();

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
   *
   * @return array
   *   Orders.
   */
  public function getOrders(int $customer_id) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $cid = 'orders_list_' . $langcode . '_' . $customer_id;

    if ($cache = $this->cache->get($cid)) {
      return $cache->data;
    }

    try {
      $query = $this->getOrdersQuery('customer_id', $customer_id);

      $request_options = [
        'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_search'),
      ];

      $response = $this->apiWrapper->invokeApi('orders', $query, 'GET', FALSE, $request_options);
      $result = json_decode($response ?? [], TRUE);
      $orders = $result['items'] ?? [];
      foreach ($orders as $key => $order) {
        // Allow other modules to alter order details.
        \Drupal::moduleHandler()->alter('alshaya_acm_customer_order_details', $order);
        $orders[$key] = $this->cleanupOrder($order);
      }
    }
    catch (\Exception $e) {
      // Exception message is already added to log in APIWrapper.
      $orders = [];
    }

    // Sort them by default by date.
    usort($orders, function ($a, $b) {
      return $b['created_at'] > $a['created_at'];
    });

    // Get the cache expiration time based on config value.
    $cacheTimeLimit = $this->config->get('cache_time_limit');

    // We can disable caching via config by setting it to zero.
    if ($cacheTimeLimit > 0) {
      $expire = strtotime('+' . $cacheTimeLimit . ' seconds');

      // Store in cache.
      $this->cache->set($cid, $orders, $expire);
    }

    // Re-set count again.
    $this->countCache->set('orders_count_' . $customer_id, count($orders));

    return $orders;
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
    $result = json_decode($response ?? [], TRUE);
    $count = $result['total_count'] ?? 0;
    $this->countCache->set($cid, $count);

    return $count;
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
    $result = json_decode($response ?? [], TRUE);
    $count = $result['total_count'] ?? 0;
    if (empty($count)) {
      return NULL;
    }

    $order = reset($result['items']);
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
    $result = json_decode($response ?? [], TRUE);
    $count = $result['total_count'] ?? 0;
    if (empty($count)) {
      return NULL;
    }

    $order = reset($result['items']);
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

}
