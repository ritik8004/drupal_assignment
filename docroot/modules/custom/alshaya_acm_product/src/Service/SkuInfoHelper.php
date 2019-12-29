<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\StockManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\metatag\MetatagManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Class SkuInfoHelper.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuInfoHelper {

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SKU images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * The Metatag manager.
   *
   * @var \Drupal\metatag\MetatagManager
   */
  protected $metatagManager;

  /**
   * Price Helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuPriceHelper
   */
  protected $priceHelper;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Stock manager.
   *
   * @var \Drupal\acq_sku\StockManager
   */
  protected $acqSkuStockManager;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * SkuInfoHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\metatag\MetatagManager $metatagManager
   *   The metatag manager object.
   * @param \Drupal\alshaya_acm_product\Service\SkuPriceHelper $price_helper
   *   Price Helper.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\Core\Database\Connection $database
   *   The database object.
   * @param \Drupal\acq_sku\StockManager $acq_stock_manager
   *   The stock manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(
    SkuManager $sku_manager,
    SkuImagesManager $sku_images_manager,
    MetatagManager $metatagManager,
    SkuPriceHelper $price_helper,
    RendererInterface $renderer,
    ModuleHandlerInterface $module_handler,
    Connection $database,
    StockManager $acq_stock_manager,
    LanguageManagerInterface $language_manager
  ) {
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->metatagManager = $metatagManager;
    $this->priceHelper = $price_helper;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
    $this->database = $database;
    $this->acqSkuStockManager = $acq_stock_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * Get translation of given entity for given langcode.
   *
   * @param object $entity
   *   The entity object.
   * @param string $langcode
   *   The language code.
   *
   * @return object
   *   Return entity object with translation if exists otherwise as is.
   */
  public function getEntityTranslation($entity, $langcode) {
    if (($entity instanceof ContentEntityInterface
         || $entity instanceof ConfigEntityInterface)
        && $entity->language()->getId() != $langcode
        && $entity->hasTranslation($langcode)
    ) {
      $entity = $entity->getTranslation($langcode);
    }
    return $entity;
  }

  /**
   * Get the size attributes with code and label.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Return the keyed array of size attributes code and label.
   */
  public function getSizeLabels(SKUInterface $sku): array {
    $size_array = &drupal_static(__METHOD__, []);

    if ($sku->bundle() == 'simple') {
      $plugin = $sku->getPluginInstance();
      if (($parent = $plugin->getParentSku($sku)) && $parent instanceof SKUInterface) {
        $sku = $parent;
      }
    }
    $sku_string = $sku->get('sku')->getString();

    if (!isset($size_array[$sku_string])) {
      $configurables = unserialize(
        $sku->get('field_configurable_attributes')->getString()
      );

      if (empty($configurables)) {
        return [];
      }

      $size_key = array_search('size', array_column($configurables, 'label'));
      if (!isset($configurables[$size_key])) {
        return [];
      }

      $size_options = [];
      array_walk($configurables[$size_key]['values'], function ($value, $key) use (&$size_options) {
        $size_options[$value['value_id']] = $value['label'];
      });
      $size_array[$sku_string] = $size_options;
    }

    return $size_array[$sku_string];
  }

  /**
   * Wrapper function get attributes.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param array $unused_options
   *   (Optional) any extra unused options.
   *
   * @return array
   *   Attributes.
   */
  public function getAttributes(SKUInterface $sku, array $unused_options = []): array {
    $skuData = $sku->toArray();

    $attributes = [];
    foreach ($skuData['attributes'] as $row) {
      if (!empty($unused_options) && in_array($row['key'], $unused_options)) {
        continue;
      }

      // Can not use data from $skuData['attributes'] as it is key_value
      // field type, and value is varchar field with limit of 255, Which strips
      // the text beyond the limit for description, and some of fields have key
      // stored instead of value, value is saved in it's separate table.
      if (isset($skuData["attr_{$row['key']}"])) {
        $row['value'] = $sku->get("attr_{$row['key']}")->getString();
      }
      // Remove un-wanted description key.
      unset($row['description']);
      $attributes[] = $row;
    };

    return $attributes;
  }

  /**
   * Get the metatags info of given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param array $selected_tags
   *   (Optional) If given, process only selected tags.
   *
   * @return array
   *   Return the array of metatags.
   */
  public function metaTags(NodeInterface $node, array $selected_tags = []) {
    $tags = $this->metatagManager->tagsFromEntityWithDefaults($node);
    if (!empty($selected_tags)) {
      $tags = array_intersect_key($tags, array_combine($selected_tags, $selected_tags));
    }
    $metaTags = $this->metatagManager->generateRawElements($tags, $node);

    $return = [];
    if (!empty($metaTags)) {
      foreach ($metaTags as $key => $tag) {
        $return[$key] = $tag['#attributes']['content'];
      }
    }

    return $return;
  }

  /**
   * Return formatted price.
   *
   * @param float $price
   *   The price.
   *
   * @return string
   *   Return string price upto configured decimal points.
   */
  public function formatPriceDisplay(float $price): string {
    return (string) _alshaya_acm_format_price_with_decimal($price);
  }

  /**
   * Wrapper function to get media items for an SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Media Items.
   */
  public function getMedia(SKUInterface $sku, string $context): array {
    /** @var \Drupal\acq_sku\Entity\SKU $sku */
    $media = $this->skuImagesManager->getProductMedia($sku, $context);

    $return = [
      'images' => [],
      'videos' => [],
    ];

    foreach ($media['media_items']['images'] ?? [] as $media_item) {
      $return['images'][] = [
        'url' => file_create_url($media_item['drupal_uri']),
        'image_type' => $media_item['sortAssetType'] ?? 'image',
      ];
    }

    foreach ($media['media_items']['videos'] ?? [] as $media_item) {
      $return['videos'][] = $media_item['video_url'];
    }

    return $return;
  }

  /**
   * Get categories associated with node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param string|null $lang
   *   The lang code.
   *
   * @return array
   *   The array of terms with name, id and url.
   */
  public function getProductCategories(NodeInterface $node, $lang = NULL) {
    $categories = $node->get('field_category')->referencedEntities();
    $terms = [];
    if (!empty($categories)) {
      foreach ($categories as $term) {
        $term = $this->getEntityTranslation($term, $lang);
        $terms[] = [
          'name' => $term->label(),
          'id' => $term->id(),
          'url' => $this->getEntityUrl($term),
        ];
      }
    }
    return $terms;
  }

  /**
   * Get the entity Url.
   *
   * @param object $entity
   *   The entity object.
   * @param bool $absolute
   *   (Optional) true to get absolute url, otherwise false.
   *
   * @return mixed
   *   Return the generate url of the entity.
   */
  public function getEntityUrl($entity, $absolute = TRUE) {
    return $entity->toUrl('canonical', ['absolute' => $absolute])
      ->toString(TRUE)
      ->getGeneratedUrl();
  }

  /**
   * Get the stock info.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku_entity
   *   The sku entity.
   *
   * @return array
   *   The array with in_stock and stock.
   */
  public function stockInfo(SKUInterface $sku_entity): array {
    return [
      'in_stock' => $this->skuManager->isProductInStock($sku_entity),
      'stock' => (float) $this->skuManager->getStockQuantity($sku_entity),
    ];
  }

  /**
   * Function get linked skus.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $linked_type
   *   Linked type.
   *
   * @return array
   *   Linked SKUs.
   */
  public function getLinkedSkus(SKUInterface $sku, string $linked_type) {
    $linkedSkus = $this->skuManager->getLinkedSkus($sku, $linked_type);
    $linkedSkus = $this->skuManager->filterRelatedSkus($linkedSkus);

    return $linkedSkus;
  }

  /**
   * Get variants data for configurable product.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Data for all variants.
   */
  public function getConfigurableProductData(SKUInterface $sku, string $context) {
    if ($sku->bundle() !== 'configurable') {
      return [];
    }

    $variants = [];

    $pdp_layout = $this->skuManager->getPdpLayout($sku, $context);
    $combinations = $this->skuManager->getConfigurableCombinations($sku);
    foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
      $child = SKU::loadFromSku($child_sku);
      if (!$child instanceof SKUInterface) {
        continue;
      }

      $variants[$child->getSku()] = $this->getVariantInfo($child, $pdp_layout, $sku);
    }

    return $variants;
  }

  /**
   * Wrapper function to get variant info.
   *
   * @param \Drupal\acq_commerce\SKUInterface $child
   *   Product.
   * @param string $pdp_layout
   *   PDP Layout.
   * @param \Drupal\acq_commerce\SKUInterface|null $parent
   *   Parent product if available.
   *
   * @return array
   *   Variant info.
   */
  public function getVariantInfo(SKUInterface $child, string $pdp_layout, ?SKUInterface $parent = NULL) {
    $stockInfo = $this->stockInfo($child);
    $price = $this->priceHelper->getPriceBlockForSku($child);
    $gallery = $this->skuImagesManager->getGallery($child, $pdp_layout, $child->label(), FALSE);

    $variant = [];
    $variant['id'] = (int) $child->id();
    $variant['sku'] = (string) $child->getSku();
    $variant['stock'] = [
      'status' => (int) $stockInfo['in_stock'],
      'qty' => (float) $stockInfo['stock'],
    ];
    $variant['price'] = $this->renderer->renderPlain($price);
    $variant['gallery'] = !empty($gallery) ? $this->renderer->renderPlain($gallery) : '';
    $variant['layout'] = $pdp_layout;

    $this->moduleHandler->alter('sku_variant_info', $variant, $child, $parent);

    return $variant;
  }

  /**
   * Return stock for given sku entity.
   */
  public function calculateStock(SKU $sku) {
    $sku_string = $sku->getSku();

    $static = &drupal_static(__METHOD__, []);
    if (isset($static[$sku_string])) {
      return $static[$sku_string];
    }

    // Return quantity of given SKU.
    switch ($sku->bundle()) {
      case 'configurable':
        $configured_skus = $sku->get('field_configured_skus')->getValue();
        $child_skus = array_map(function ($item) {
          return $item['value'];
        }, $configured_skus);

        $query = $this->database->select('acq_sku_stock', 'stock');
        $query->addExpression('SUM(stock.quantity)', 'final_quantity');
        $query->condition('stock.sku', $child_skus, 'IN');
        $query->condition('stock.status', 1);
        $static[$sku_string] = $query->execute()->fetchField();
        break;

      case 'simple':
        $static[$sku_string] = $this->acqSkuStockManager->getStockQuantity($sku->getSku());
        break;
    }
    return $static[$sku_string];
  }

  /**
   * Get Light Product.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $color
   *   Color value.
   *
   * @return array
   *   Light Product.
   */
  public function getLightProduct(SKUInterface $sku, string $color = ''): array {
    $node = $this->skuManager->getDisplayNode($sku);
    if (!($node instanceof NodeInterface) || !($node->hasTranslation($this->languageManager->getCurrentLanguage()->getId()))) {
      return [];
    }
    // Get the prices.
    $prices = $this->skuManager->getMinPrices($sku, $color);
    // Get the promotion data.
    $promotions = $this->skuManager->getPromotions($sku);
    // Get promo labels.
    $promo_label = $this->skuManager->getDiscountedPriceMarkup($prices['price'], $prices['final_price']);
    if ($promo_label) {
      $promotions[] = [
        'text' => $promo_label,
      ];
    }
    // Get label for the SKU.
    $labels = $this->skuManager->getSkuLabels($sku, 'plp');
    // Get media (images/video) for the SKU.
    $sku_for_gallery = $this->skuImagesManager->getSkuForGalleryWithColor($sku, $color) ?? $sku;
    $images = $this->getMedia($sku_for_gallery, 'search');
    $data = [
      'id' => (int) $sku->id(),
      'title' => $sku->label(),
      'sku' => $sku->getSku(),
      'link' => $this->getEntityUrl($node),
      'original_price' => $this->formatPriceDisplay($prices['price']),
      'final_price' => $this->formatPriceDisplay($prices['final_price']),
      'in_stock' => $this->skuManager->isProductInStock($sku),
      'promo' => $promotions,
      'medias' => $images,
      'labels' => $labels,
      'color' => NULL,
    ];
    if ($color) {
      $data['color'] = $color;
    }
    // Allow other modules to alter light product data.
    $type = 'light';
    $this->moduleHandler->alter('alshaya_acm_product_light_product_data', $sku, $data, $type);
    return $data;
  }

}
