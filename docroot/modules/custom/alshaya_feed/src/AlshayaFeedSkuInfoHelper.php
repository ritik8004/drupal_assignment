<?php

namespace Drupal\alshaya_feed;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
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
    TimeInterface $date_time
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
        'attributes' => $this->skuInfoHelper->getAttributes($sku, ['description']),
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
            'swatch_image' => $this->skuImagesManager->getPdpSwatchImageUrl($child) ?? [],
            'images' => $this->skuImagesManager->getGalleryMedia($child, FALSE),
            'stock' => [
              'status' => $stockInfo['in_stock'],
              'qty' => $stockInfo['stock'],
            ],
          ];
          $product[$lang]['variants'][] = $variant;
        }
      }

      // Display swatches only if enabled in configuration and not color node.
      if ($this->configFactory->get('alshaya_acm_product.display_settings')->get('color_swatches') && empty($color)) {
        // Get swatches for this product from media.
        $product[$lang]['swatches'] = $this->skuImagesManager->getSwatches($sku);
      }

      foreach ($this->linkedTypes as $linked_type_key => $linked_type) {
        $linked_skus = $this->skuInfoHelper->getLinkedSkus($sku, $linked_type);
        $product[$lang][$linked_type_key] = array_keys($linked_skus);
      }

      // Allow other modules to alter light product data.
      $this->moduleHandler->alter('alshaya_mobile_app_light_product_data', $sku, $product[$lang]);
    }

    // Cache only for XX mins.
    $expire = $this->dateTime->getRequestTime() + $this->cacheTime;
    $this->cache->set($cache_id, $product, $expire, [
      'node:' . $nid,
    ]);

    return $product;
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
  protected function getConfigurableValues(SKUInterface $sku, array $attributes = []): array {
    if ($sku->bundle() !== 'simple') {
      return [];
    }

    $labels = [
      'attr_color_label' => 'color',
      'attr_size' => 'size',
    ];

    $values = $this->skuManager->getConfigurableValues($sku);
    foreach ($values as $attribute_code => &$value) {
      $value['label'] = $labels[$attribute_code] ?? $value['label'];
      $value['attribute_code'] = $attribute_code;

      if (($attr_value = $attributes[str_replace('attr_', '', $attribute_code)]) && !is_numeric($attr_value)) {
        $value['value'] = (string) $attr_value;
      }
      elseif (str_replace('attr_', '', $attribute_code) == 'size' && is_numeric($values['value'])) {
        $size_labels = $this->skuInfoHelper->getSizeLabels($sku);
        $value['value'] = $size_labels[$attributes[str_replace('attr_', '', $attribute_code)]] ?? $attributes[str_replace('attr_', '', $attribute_code)];
      }
    }

    return array_values($values);
  }

}
