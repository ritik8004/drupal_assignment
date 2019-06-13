<?php

namespace Drupal\alshaya_acm_customer;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class OrdersManager.
 *
 * @TODO: Move all code from utility file to here.
 * Target file alshaya_acm_customer.orders.inc.
 *
 * @package Drupal\alshaya_acm_customer
 */
class OrdersManager {

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
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
   * OrdersManager constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
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
  public function __construct(APIWrapper $api_wrapper,
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
  }

  /**
   * Helper function to clear orders related cache for a user/email.
   *
   * @param string $email
   *   Email for which cache needs to be cleared.
   * @param int $uid
   *   User id for which cache needs to be cleared.
   */
  public function clearOrderCache($email, $uid = 0) {
    // Clear user's order cache.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $this->cache->delete('orders_list_' . $langcode . '_' . $email);
    }

    // Clear user's order count cache.
    $this->countCache->delete('orders_count_' . $email);

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
   * @param string $email
   *   E-Mail address.
   *
   * @return array
   *   Orders.
   */
  public function getOrders(string $email) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $cid = 'orders_list_' . $langcode . '_' . $email;

    if ($cache = $this->cache->get($cid)) {
      $orders = $cache->data;
    }
    else {
      try {
        $orders = $this->apiWrapper->getCustomerOrders($email);
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

      // Verify count again and reset if required.
      if (count($orders) != $this->getOrdersCount($email)) {
        $this->countCache->set('orders_count_' . $email, count($orders));
      }
    }

    return $orders;
  }

  /**
   * Get orders count.
   *
   * We need only count for some cases like GTM and count won't change like
   * orders so we store them permanently.
   *
   * @param string $email
   *   E-Mail address.
   *
   * @return int
   *   Orders count.
   */
  public function getOrdersCount(string $email) {
    $cid = 'orders_count_' . $email;

    if ($cache = $this->countCache->get($cid)) {
      $count = $cache->data;
    }
    else {
      $orders = $this->getOrders($email);
      $count = count($orders);
      $this->countCache->set($cid, $count);
    }

    return $count;
  }

}
