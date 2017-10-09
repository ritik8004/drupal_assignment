<?php

namespace Drupal\alshaya_acm;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * ApiHelper.
 */
class ApiHelper {

  /**
   * APIWrapper service object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * The cart storage service.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * The cart object.
   *
   * @var \Drupal\acq_cart\CartInterface
   */
  protected $cart;

  /**
   * Current Language code.
   *
   * @var string
   */
  protected $langcode;

  /**
   * API Helper cache object.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Time to cache the API response.
   *
   * @var int
   */
  protected $cacheTime;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   APIWrapper service object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   LanguageManagerInterface object.
   */
  public function __construct(APIWrapper $api_wrapper,
                              CartStorageInterface $cart_storage,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache,
                              LanguageManagerInterface $language_manager) {
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->cart = $this->cartStorage->getCart(FALSE);
    $this->cacheTime = (int) $config_factory->get('alshaya_acm.settings')->get('api_cache_time');
    $this->cache = $cache;
    $this->langcode = $language_manager->getCurrentLanguage()->getId();
  }

  /**
   * Wrapper function to get payment methods from API.
   *
   * @return array
   *   Payment methods.
   */
  public function getPaymentMethods() {
    if (empty($this->cart)) {
      return [];
    }

    $cache_id = 'pm_' . $this->langcode . '_';
    $cache_id .= $this->cart->id() . '_';
    $cache_id .= $this->cart->getShippingMethodAsString();

    if ($cache = $this->cache->get($cache_id)) {
      return $cache->data;
    }

    $methods = $this->apiWrapper->getPaymentMethods($this->cart->id());

    // Cache only for XX mins.
    $expire = \Drupal::time()->getRequestTime() + $this->cacheTime;

    $this->cache->set($cache_id, $methods, $expire);

    return $methods;
  }

  /**
   * Wrapper function to get shipping methods with estimated costs from API.
   *
   * @param array|object $address
   *   Array with the target address.
   *
   * @return array
   *   Shipping methods with estimated costs.
   */
  public function getShippingEstimates($address) {
    if (empty($this->cart)) {
      return [];
    }

    $cache_id = 'sm_' . $this->langcode . '_';
    $cache_id .= $this->cart->id() . '_';
    $cache_id .= md5(json_encode($address));

    if ($cache = $this->cache->get($cache_id)) {
      return $cache->data;
    }

    $methods = $this->apiWrapper->getShippingEstimates($this->cart->id(), $address);

    // Cache only for XX mins.
    $expire = \Drupal::time()->getRequestTime() + $this->cacheTime;

    $this->cache->set($cache_id, $methods, $expire);

    return $methods;
  }

}
