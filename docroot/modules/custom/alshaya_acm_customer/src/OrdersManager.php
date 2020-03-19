<?php

namespace Drupal\alshaya_acm_customer;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_api\AlshayaApiWrapper;
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
        $query = $this->getOrdersQuery($email);
        $response = $this->apiWrapper->invokeApi('orders', $query, 'GET');
        $result = json_decode($response ?? [], TRUE);
        $orders = $result['items'] ?? [];
        foreach ($orders as $key => $order) {
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
      $query = $this->getOrdersQuery($email);
      $query['searchCriteria']['pageSize'] = 1;
      $response = $this->apiWrapper->invokeApi('orders', $query, 'GET');
      $result = json_decode($response ?? [], TRUE);
      $count = $result['total_count'] ?? 0;
      $this->countCache->set($cid, $count);
    }

    return $count;
  }

  /**
   * Helper function to return order from session.
   *
   * @return array
   *   Order array if found.
   */
  public function getOrder($order_id) {
    $endpoint = str_replace('{id}', $order_id, 'orders/{id}');
    $order = $this->apiWrapper->invokeApi($endpoint, [], 'GET');
    $order = json_decode($order, TRUE);

    if (empty($order)) {
      return NULL;
    }

    return $this->cleanupOrder($order);
  }

  /**
   * Cleanup order array as expected by Drupal.
   *
   * @param array $order
   *   Order array.
   *
   * @return array
   *   Cleaned up order array.
   */
  private function cleanupOrder(array $order) {
    $order['order_id'] = $order['entity_id'];

    // Customer info.
    $order['firstname'] = $order['customer_firstname'];
    $order['lastname'] = $order['customer_lastname'];
    $order['email'] = $order['customer_email'];

    $items = [];
    foreach ($order['items'] as $item) {
      $processed_item = [
        'type' => (string) ($item['product_type'] ?? ''),
        'price' => ($item['price_incl_tax'] ?? 0),
        'price_without_tax' => ($item['price'] ?? 0),
        'ordered' => (int) ($item['qty_ordered'] ?? 0),
        'shipped' => (int) ($item['qty_shipped'] ?? 0),
        'refunded' => (int) ($item['qty_refunded'] ?? 0),
      ];

      // Add all other info.
      $items[] = array_merge($processed_item, $item);
    }
    $order['items'] = $items;

    $order['coupon'] = $order['coupon_code'] ?? '';

    // Extension.
    $order['extension'] = $order['extension_attributes'];
    unset($order['extension_attributes']);

    // Shipping.
    $order['shipping'] = $order['extension']['shipping_assignments'][0]['shipping'];
    $order['shipping']['address']['extension'] = $order['shipping']['address']['extension_attributes'];
    unset($order['shipping']['address']['extension_attributes']);

    // Billing.
    $order['billing'] = $order['billing_address'];
    unset($order['billing_address']);
    $order['billing']['extension'] = $order['billing']['extension_attributes'];
    unset($order['billing']['extension_attributes']);

    $order['totals'] = [
      'sub' => ($order['subtotal_incl_tax'] ?? 0),
      'tax' => ($order['tax_amount'] ?? 0),
      'discount' => ($order['discount_amount'] ?? 0),
      'shipping' => ($order['shipping_incl_tax'] ?? 0),
      'surcharge' => ($order['extension']['surcharge_incl_tax'] ?? 0),
      'grand' => ($order['grand_total'] ?? 0),
    ];

    return $order;
  }

  /**
   * Wrapper function to get orders query.
   *
   * @param string $email
   *   E-Mail address.
   *
   * @return array
   *   Orders query.
   */
  private function getOrdersQuery(string $email) {
    return [
      'searchCriteria' => [
        'filterGroups' => [
          [
            'filters' => [
              [
                'field' => 'customer_email',
                'value' => $email,
                'condition_type' => 'eq',
              ],
            ],
          ],
        ],
        'sortOrders' => [
          ['field' => 'created_at', 'direction' => 'DESC'],
        ],
      ],
    ];
  }

}
