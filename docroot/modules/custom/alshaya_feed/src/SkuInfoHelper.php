<?php

namespace Drupal\alshaya_feed;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Class SkuInfoHelper.
 *
 * @todo: Almost all methods are copied from MobileAppUtility.php, convert it
 * to Unique service to be used for ProductResource.php and feed.
 *
 * @package Drupal\alshaya_feed
 */
class SkuInfoHelper {

  use StringTranslationTrait;

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
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

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
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    SkuManager $sku_manager,
    ConfigFactory $configFactory,
    EntityRepositoryInterface $entity_repository,
    SkuImagesManager $sku_images_manager,
    ModuleHandlerInterface $module_handler
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->skuManager = $sku_manager;
    $this->configFactory = $configFactory;
    $this->entityRepository = $entity_repository;
    $this->skuImagesManager = $sku_images_manager;
    $this->moduleHandler = $module_handler;
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
    $node = $this->entityTypeManager->getStorage('node')->load($nid);

    if (!$node instanceof NodeInterface) {
      return [];
    }
    $color = ($this->skuManager->isListingModeNonAggregated()) ? $node->get('field_product_color')->getString() : '';

    // Get SKU attached with node.
    $sku = $this->skuManager->getSkuForNode($node);
    $sku = SKU::loadFromSku($sku);

    if (!$sku instanceof SKU) {
      return [];
    }

    // Get the prices.
    $prices = $this->skuManager->getMinPrices($sku, $color);
    $sku_for_gallery = $this->skuImagesManager->getSkuForGalleryWithColor($sku, $color) ?? $sku;

    $stockInfo = $this->stockInfo($sku);
    $meta_tags = $this->metaTags($node);

    $product = [];
    foreach ($this->languageManager->getLanguages() as $lang => $language) {
      if (($node->language()->getId() !== $lang) && $node->hasTranslation($lang)) {
        $node = $node->getTranslation($lang);
      }

      if (($node->language()->getId() !== $lang) && $sku->hasTranslation($lang)) {
        $sku = $sku->getTranslation($lang);
      }

      $product[$lang] = [
        'name' => $node->label(),
        'sku' => $sku->getSku(),
        'type_id' => $sku->bundle(),
        'short_description' => !empty($node->get('body')->first()) ? $node->get('body')->first()->getValue()['summary'] : '',
        'description' => !empty($node->get('body')->first()) ? $this->convertRelativeUrlsToAbsolute($node->get('body')->first()->getValue()['value']) : '',
        'images' => $this->getMedia($sku_for_gallery, 'pdp')['images'],
        'original_price' => $this->formatPriceDisplay($prices['price']),
        'final_price' => $this->formatPriceDisplay($prices['final_price']),
        'link' => $this->getEntityUrl($node),
        'stock' => [
          'status' => $stockInfo['in_stock'],
          'qty' => $stockInfo['stock'],
        ],
        'categoryCollection' => $this->getCategories($node, $lang),
        'meta_description' => $meta_tags['meta_description'] ?? '',
        'meta_keyword' => $meta_tags['meta_keyword'] ?? '',
        'meta_title' => $meta_tags['meta_title'] ?? '',
        'attributes' => $this->getAttributes($sku),
      ];

      if ($sku->bundle() === 'configurable') {
        $combinations = $this->skuManager->getConfigurableCombinations($sku);

        foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
          $child = SKU::loadFromSku($child_sku);
          if (!$child instanceof SKUInterface) {
            continue;
          }
          $stockInfo = $this->stockInfo($child);
          $variant = [
            'sku' => $child->getSku(),
            'configurable_attributes' => $this->skuManager->getConfigurableAttributes($child),
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

      $product[$lang]['relatedProducts'] = $this->skuManager->getLinkedSkus($sku, 'related');
      $product[$lang]['crossSellProducts'] = $this->skuManager->getLinkedSkus($sku, 'crosssell');
      $product[$lang]['upSellProducts'] = $this->skuManager->getLinkedSkus($sku, 'upsell');

      // Allow other modules to alter light product data.
      $this->moduleHandler->alter('alshaya_mobile_app_light_product_data', $sku, $product[$lang]);
    }

    return $product;
  }

  /**
   * Wrapper function get attributes.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Attributes.
   */
  public function getAttributes(SKUInterface $sku): array {
    $skuData = $sku->toArray();

    // @TODO: We should really think of returning what is required instead
    // of removing stuff. Can be done later when we start developing and testing
    // for all brands.
    $unused_options = [
      'options_container',
      'required_options',
      'has_options',
      'product_activation_date',
      'url_key',
      'msrp_display_actual_price_type',
      'product_state',
      'tax_class_id',
      'gift_message_available',
      'gift_wrapping_available',
      'is_returnable',
      'special_from_date',
      'special_to_date',
      'custom_design_from',
      'custom_design_to',
      'ignore_price_update',
      'image',
      'small_image',
      'thumbnail',
      'swatch_image',
      'meta_description',
      'meta_keyword',
      'meta_title',
    ];

    $attributes = [];
    foreach ($skuData['attributes'] as $row) {
      if (in_array($row['key'], $unused_options)) {
        continue;
      }

      // Can not use data from $skuData['attributes'] as it is key_value
      // field type, and value is varchar field with limit of 255, Which strips
      // the text beyond the limit for description, and some of fields have key
      // stored instead of value, value is saved in it's separate table.
      if (isset($skuData["attr_{$row['key']}"])) {
        $row['value'] = $skuData["attr_{$row['key']}"][0]['value'];
      }
      // Remove un-wanted description key.
      unset($row['description']);
      $attributes[$row['key']] = $row['value'];
    };

    return $attributes;
  }

  /**
   * Convert relative url img tag in string with absolute url.
   *
   * @param string $string
   *   The string containing html tags.
   *
   * @return string
   *   Return the complete url string with domain.
   */
  public function convertRelativeUrlsToAbsolute(string $string): string {
    global $base_url;
    return preg_replace('#(src)="([^:"]*)(?:")#', '$1="' . $base_url . '$2"', $string);
  }

  /**
   * Get the metatags info of given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   *
   * @return array
   *   Return the array of metatags.
   */
  public function metaTags(NodeInterface $node) {
    $metaTags = $node->get('field_meta_tags')->getValue();
    $return = [];
    if (!empty($metaTags)) {

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
    ];

    foreach ($media['media_items']['images'] ?? [] as $media_item) {
      $return['images'][] = [
        'url' => file_create_url($media_item['drupal_uri']),
        'image_type' => $media_item['sortAssetType'] ?? 'image',
        'label' => $media_item['label'] ?? '',
      ];
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
  public function getCategories(NodeInterface $node, $lang = NULL) {
    $categories = $node->get('field_category')->referencedEntities();
    $terms = [];
    if (!empty($categories)) {
      foreach ($categories as $term) {
        if (!empty($lang) && $term->hasTranslation($lang)) {
          $term = $term->getTranslation($lang);
        }
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
   *
   * @return mixed
   *   Return the generate url of the entity.
   */
  public function getEntityUrl($entity) {
    return $entity->toUrl('canonical', ['absolute' => TRUE])
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
      'stock' => $this->skuManager->getStockQuantity($sku_entity),
    ];
  }

}
