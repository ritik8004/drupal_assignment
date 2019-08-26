<?php

namespace Drupal\alshaya_feed;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;

/**
 * Class SkuInfoHelper.
 *
 * @package Drupal\alshaya_feed
 */
class AlshayaFeedSkuInfoHelper {

  /**
   * Entity Type Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * The Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * SKU images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

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
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * Associative array of linked product types.
   *
   * @var array
   */
  protected $linkedTypes = [
    'relatedProducts' => LINKED_SKU_TYPE_RELATED,
    'crossSellProducts' => LINKED_SKU_TYPE_UPSELL,
    'upSellProducts' => LINKED_SKU_TYPE_CROSSSELL,
  ];

  /**
   * SkuInfoHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    SkuManager $sku_manager,
    ConfigFactory $configFactory,
    SkuImagesManager $sku_images_manager,
    ModuleHandlerInterface $module_handler,
    SkuInfoHelper $sku_info_helper,
    ConfigFactoryInterface $config_factory,
    CacheBackendInterface $cache,
    TimeInterface $date_time,
    SKUFieldsManager $sku_fields_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->skuManager = $sku_manager;
    $this->configFactory = $configFactory;
    $this->skuImagesManager = $sku_images_manager;
    $this->moduleHandler = $module_handler;
    $this->skuInfoHelper = $sku_info_helper;
    $this->cacheTime = (int) $config_factory->get('alshaya_feed.settings')->get('cache_time');
    $this->cache = $cache;
    $this->dateTime = $date_time;
    $this->skuFieldsManager = $sku_fields_manager;
  }

  /**
   * Process given nid and get product related info.
   *
   * @param int $nid
   *   The product node id.
   *
   * @return array
   *   Return the array of product data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function process(int $nid): array {
    $cache_id = 'alshaya_feed_' . $nid;
    if ($cache = $this->cache->get($cache_id)) {
      return $cache->data;
    }

    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if (!$node instanceof NodeInterface) {
      return [];
    }

    // Get SKU attached with node.
    $sku = $this->skuManager->getSkuForNode($node);
    $sku = SKU::loadFromSku($sku);
    if (!$sku instanceof SKU) {
      return [];
    }

    $product = [];
    foreach ($this->languageManager->getLanguages() as $lang => $language) {
      $node = $this->skuInfoHelper->getEntityTranslation($node, $lang);
      $sku = $this->skuInfoHelper->getEntityTranslation($sku, $lang);

      // Get the prices.
      $color = ($this->skuManager->isListingModeNonAggregated()) ? $node->get('field_product_color')->getString() : '';
      $prices = $this->skuManager->getMinPrices($sku, $color);
      $sku_for_gallery = $this->skuImagesManager->getSkuForGalleryWithColor($sku, $color) ?? $sku;

      $stockInfo = $this->skuInfoHelper->stockInfo($sku);
      $meta_tags = $this->skuInfoHelper->metaTags($node, [
        'title',
        'description',
        'keywords',
      ]);

      $product[$lang] = [
        'sku' => $sku->getSku(),
        'name' => $node->label(),
        'type_id' => $sku->bundle(),
        'status' => (bool) $node->isPublished(),
        'url' => $this->skuInfoHelper->getEntityUrl($node),
        'short_description' => !empty($node->get('body')->first()) ? $node->get('body')->first()->getValue()['summary'] : '',
        'description' => !empty($node->get('body')->first()) ? $node->get('body')->first()->getValue()['value'] : '',
        'images' => $this->skuInfoHelper->getMedia($sku_for_gallery, 'pdp')['images'],
        'original_price' => $this->skuInfoHelper->formatPriceDisplay((float) $prices['price']),
        'final_price' => $this->skuInfoHelper->formatPriceDisplay((float) $prices['final_price']),
        'stock' => [
          'status' => $stockInfo['in_stock'],
          'qty' => $stockInfo['stock'],
        ],
        'categoryCollection' => $this->skuInfoHelper->getProductCategories($node, $lang),
        'meta_description' => $meta_tags['description'] ?? '',
        'meta_keywords' => $meta_tags['keywords'] ?? '',
        'meta_title' => $meta_tags['title'] ?? '',
        'attributes' => $this->skuInfoHelper->getAttributes($sku, ['description', 'short_description']),
      ];

      if ($sku->bundle() === 'configurable') {
        $combinations = $this->skuManager->getConfigurableCombinations($sku);

        foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
          $child = SKU::loadFromSku($child_sku);
          if (!$child instanceof SKUInterface) {
            continue;
          }
          $stockInfo = $this->skuInfoHelper->stockInfo($child);

          $variant = [
            'sku' => $child->getSku(),
            'configurable_attributes' => $this->getConfigurableValues($child, $combination),
            'swatch_image' => $this->getSwatchImages($sku, $combination),
            'images' => $this->getGalleryMedia($child),
            'stock' => [
              'status' => $stockInfo['in_stock'],
              'qty' => $stockInfo['stock'],
            ],
          ];
          $product[$lang]['variants'][] = $variant;
        }
      }

      foreach ($this->linkedTypes as $linked_type_key => $linked_type) {
        $linked_skus = $this->skuInfoHelper->getLinkedSkus($sku, $linked_type);
        $product[$lang][$linked_type_key] = array_keys($linked_skus);
      }
    }

    // Cache only for XX mins.
    $expire = $this->dateTime->getRequestTime() + $this->cacheTime;
    $this->cache->set($cache_id, $product, $expire, [
      'node:' . $nid,
    ]);

    return $product;
  }

  /**
   * Wrapper function get swatch images.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param array $combination
   *   Array of combination attributes.
   *
   * @return array
   *   An array swatch image with label.
   */
  protected function getSwatchImages(SKUInterface $sku, array $combination): array {
    $swatches = $this->skuImagesManager->getSwatchData($sku);
    $swatch_image = [];
    if (!empty($swatches) && isset($combination[$swatches['attribute_code']])) {
      $swatch_image = $swatches['swatches'][$combination[$swatches['attribute_code']]] ?? [];
    }
    return $swatch_image;
  }

  /**
   * Wrapper function get gallery images.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Configurable Values.
   */
  protected function getGalleryMedia(SKUInterface $sku): array {
    $media_items = $this->skuImagesManager->getGalleryMedia($sku, FALSE);
    if (empty($media_items) || empty($media_items['media_items'])) {
      return [];
    }

    return array_map(function ($image) {
      return [
        'label' => $image['label'],
        'url' => file_create_url($image['drupal_uri']),
      ];
    }, $media_items['media_items']['images']);
  }

  /**
   * Wrapper function get configurable values.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param array $attributes
   *   Array of attributes containing attribute code and value.
   *
   * @return array
   *   Configurable Values.
   */
  public function getConfigurableValues(SKUInterface $sku, array $attributes = []): array {
    if ($sku->bundle() !== 'simple') {
      return [];
    }

    $configurableFieldValues = [];
    $remove_not_required_option = $this->skuManager->isNotRequiredOptionsToBeRemoved();
    foreach ($attributes as $code => $id) {
      $fieldKey = 'attr_' . $code;

      if ($sku->hasField($fieldKey)) {
        $value = $sku->get($fieldKey)->getString();
        if ($remove_not_required_option && $this->skuManager->isAttributeOptionToExclude($value)) {
          continue;
        }
        $configurableFieldValues[$code] = $value;
      }
      else {
        $sku_attributes = $this->getSkuAttributes($sku);
        if (empty($sku_attributes)
            || !isset($sku_attributes[$code][$id])
            || ($remove_not_required_option && $this->skuManager->isAttributeOptionToExclude($sku_attributes[$code][$id]))
        ) {
          continue;
        }
        $configurableFieldValues[$code] = $sku_attributes[$code][$id];
      }
    }

    return $configurableFieldValues;
  }

  /**
   * Get the attributes of given sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Return array of attributess.
   */
  public function getSkuAttributes(SKUInterface $sku): array {
    $static = &drupal_static(__METHOD__, []);

    $parent = $this->skuManager->getParentSkuBySku($sku, $sku->language()->getId());

    $cid = implode(':', [
      $parent->getSku(),
      $parent->language()->getId(),
    ]);

    // Do not process the same thing again and again.
    if (isset($static[$cid])) {
      return $static[$cid];
    }

    $configurables = unserialize($parent->get('field_configurable_attributes')->getString());
    if (empty($configurables) || !is_array($configurables)) {
      return [];
    }
    $configurations = [];
    foreach ($configurables as $configuration) {
      $configurations[$configuration['code']] = array_combine(array_column($configuration['values'], 'value_id'), array_column($configuration['values'], 'label'));
    }

    $static[$cid] = $configurations;
    return $configurations;
  }

}
