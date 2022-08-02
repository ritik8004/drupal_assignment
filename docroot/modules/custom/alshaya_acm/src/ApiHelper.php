<?php

namespace Drupal\alshaya_acm;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Api Helper Class.
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
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

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
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   */
  public function __construct(APIWrapper $api_wrapper,
                              CartStorageInterface $cart_storage,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache,
                              LanguageManagerInterface $language_manager,
                              TimeInterface $date_time) {
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->cacheTime = (int) $config_factory->get('alshaya_acm.settings')->get('api_cache_time');
    $this->cache = $cache;
    $this->langcode = $language_manager->getCurrentLanguage()->getId();
    $this->dateTime = $date_time;
  }

  /**
   * Wrapper function to get payment methods from API.
   *
   * @return array
   *   Payment methods.
   */
  public function getPaymentMethods() {
    // Set the cart object.
    $this->setCart();

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
    $expire = $this->dateTime->getRequestTime() + $this->cacheTime;

    $this->cache->set($cache_id, $methods, $expire, [
      'cart_extra:' . $this->cart->id(),
    ]);

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
    // Set the cart object.
    $this->setCart();

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
    $expire = $this->dateTime->getRequestTime() + $this->cacheTime;

    $this->cache->set($cache_id, $methods, $expire, [
      'cart_extra:' . $this->cart->id(),
    ]);

    return $methods;
  }

  /**
   * Sets the cart object.
   */
  protected function setCart() {
    // Set only if cart property is not set.
    if (!$this->cart) {
      $this->cart = $this->cartStorage->getCart(FALSE);
    }
  }

}
