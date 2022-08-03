<?php

namespace Drupal\acq_sku;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class Acq Sku Linked Sku.
 *
 * @package Drupal\acq_sku
 */
class AcqSkuLinkedSku {

  /**
   * For the all.
   */
  public const LINKED_SKU_TYPE_ALL = 'all';

  /**
   * For the upsell.
   */
  public const LINKED_SKU_TYPE_UPSELL = 'upsell';

  /**
   * For the cross_sell.
   */
  public const LINKED_SKU_TYPE_CROSSSELL = 'crosssell';

  /**
   * For the related.
   */
  public const LINKED_SKU_TYPE_RELATED = 'related';


  /**
   * Associative array of linked product types.
   *
   * @var array
   */
  public const LINKED_SKU_TYPES = [
    self::LINKED_SKU_TYPE_RELATED,
    self::LINKED_SKU_TYPE_UPSELL,
    self::LINKED_SKU_TYPE_CROSSSELL,
  ];

  /**
   * The conductor api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * The cache bin object.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * AcqSkuLinkedSku constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The conductor api wrapper.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache bin object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(APIWrapper $api_wrapper, CacheBackendInterface $cache, ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->cache = $cache;
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * Get linked skus for a given sku by linked type.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The sku entity.
   * @param string $type
   *   The linked type. Like - related/crosssell/upsell.
   *
   * @return array
   *   All linked skus of given type.
   */
  public function getLinkedSkus(SKU $sku, $type = self::LINKED_SKU_TYPE_ALL) {
    // Cache key is like - 'acq_sku:linked_skus:123:LINKED_SKU_TYPE_ALL'.
    $cache_key = 'acq_sku:linked_skus:' . $sku->id() . ':' . $type;

    // Get cached data.
    $cache = $this->cache->get($cache_key);

    // If already cached.
    if ($cache) {
      return $cache->data;
    }

    $data = [];

    try {
      // Get linked skus from API.
      $linked_skus = $this->apiWrapper->getLinkedskus($sku->getSku(), $type);
      $data = ($type != self::LINKED_SKU_TYPE_ALL) ? $linked_skus[$type] : $linked_skus;
    }
    catch (\Exception $e) {
      // If something bad happens, log the error.
      $this->loggerFactory->get('acq_sku')->emergency('Unable to get the @linked_sku_type linked skus for @sku : @message', [
        '@linked_sku_type' => $type,
        '@sku' => $sku->getSku(),
        '@message' => $e->getMessage(),
      ]);
    }

    $cache_lifetime = $this->configFactory
      ->get('acq_sku.settings')
      ->get('linked_skus_cache_max_lifetime');

    // Set data in cache if enabled.
    if ($cache_lifetime > 0) {
      $cache_lifetime += \Drupal::time()->getRequestTime();
      $this->cache->set($cache_key, $data, $cache_lifetime, $sku->getCacheTags());
    }

    return $data;
  }

}
