<?php

namespace Drupal\alshaya_rcs_product\Services;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\acq_sku\ProductOptionsManager;

/**
 * Contains helper methods to fetch product options.
 */
class AlshayaRcsProductAttributesHelper {

  /**
   * RCS Product Helper.
   *
   * @var \Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper
   */
  protected $rcsProductHelper;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Cache backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs Rcs Product Attribute Helper service.
   *
   * @param \Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper $rcs_product_helper
   *   RCS Product Helper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(
    AlshayaRcsProductHelper $rcs_product_helper,
    LoggerChannelFactoryInterface $logger_factory,
    CacheBackendInterface $cache,
    LanguageManagerInterface $language_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->rcsProductHelper = $rcs_product_helper;
    $this->logger = $logger_factory->get('alshaya_rcs_product');
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Returns cached product attributes options.
   */
  public function getProductAttributesOptions() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Return product options if already cached.
    $cid = 'rcs_product_options:' . $langcode;
    // Return product options if already cached already cached.
    if ($cache = $this->cache->get($cid)) {
      return $cache->data;
    }

    // Fetch product options.
    $product_attributes = $this->rcsProductHelper->getProductOptionsQueryVariables();
    $product_attributes = array_column($product_attributes, 'attribute_code');
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('field_sku_attribute_code', $product_attributes, 'IN');
    $query->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
    $query->condition('langcode', $langcode);
    $tids = $query->execute();

    // Populate and cache product options array.
    $items = [];
    if ($tids) {
      $product_option_entities = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($tids);
      foreach ($product_option_entities as $product_option) {
        $product_option = ($product_option->language()->getId() === $langcode)
          ? $product_option
          : $product_option->getTranslation($langcode);
        $product_option_en = $product_option->getTranslation('en');
        $items[$product_option->get('field_sku_attribute_code')->getString()][] = [
          'attribute_code' => $product_option->get('field_sku_attribute_code')->getString(),
          'label' => $product_option->getName(),
          'gtm_label' => $product_option_en->getName(),
          'value' => $product_option->get('field_sku_option_id')->getString(),
          'weight' => $product_option->weight->value,
        ];
      }
      $this->cache->set($cid, $items, Cache::PERMANENT, [
        'taxonomy_term:sku_product_option',
        'taxonomy_term_list:sku_product_option',
      ]);
    }

    return $items;
  }

}
