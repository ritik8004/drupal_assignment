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
   */
  public function __construct(APIWrapper $api_wrapper,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache,
                              LanguageManagerInterface $language_manager,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->config = $config_factory->get('alshaya_acm_customer.orders_config');
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->logger = $logger_factory->get('alshaya_acm_customer');
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
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $cid = 'orders_list_' . $langcode . '_' . $email;

      // Clear user's order cache.
      $this->cache->invalidate($cid);
    }

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

}
