<?php

namespace Drupal\alshaya_feed;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm\Service\AlshayaAcmApiWrapper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_i18n\AlshayaI18nLanguages;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\metatag\MetatagToken;
use Drupal\metatag\MetatagManager;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\image\Entity\ImageStyle;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Psr\Log\LoggerInterface;
use Drupal\dynamic_yield\Service\ProductDeltaFeedApiWrapper;

/**
 * Methods to prepare feed data.
 *
 * @package Drupal\alshaya_feed
 */
class AlshayaProductDeltaFeedHelper {

  /**
   * Custom table name to display OOS product SKUs.
   */
  public const OOS_SKU_TABLE_NAME = 'alshaya_feed_oos_product_skus';

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
   * SKU images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Language specific currency code.
   *
   * @var array
   */
  private static $currencyCode = [];

  /**
   * The Metatag token.
   *
   * @var \Drupal\metatag\MetatagToken
   */
  protected $tokenService;

  /**
   * The Metatag manager.
   *
   * @var \Drupal\metatag\MetatagManager
   */
  protected $metaTagManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Database connection service.
   *
   * @var \Drupal\mysql\Driver\Database\mysql\Connection
   */
  protected $connection;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * DY Product Delta Feed API Wrapper.
   *
   * @var \Drupal\dynamic_yield\Service\ProductDeltaFeedApiWrapper
   */
  protected $dyProductDeltaFeedApiWrapper;

  /**
   * AlshayaProductDeltaFeedHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\metatag\MetatagManager $metaTagManager
   *   Matatag manager.
   * @param \Drupal\metatag\MetatagToken $token
   *   The MetatagToken object.
   * @param \Drupal\mysql\Driver\Database\mysql\Connection $connection
   *   Database connection service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\dynamic_yield\Service\ProductDeltaFeedApiWrapper $product_feed_api_wrapper
   *   DY Product Delta Feed API Wrapper.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    SkuManager $sku_manager,
    SkuImagesManager $sku_images_manager,
    SkuInfoHelper $sku_info_helper,
    RendererInterface $renderer,
    ConfigFactoryInterface $config_factory,
    MetatagManager $metaTagManager,
    MetatagToken $token,
    Connection $connection,
    LoggerInterface $logger,
    ProductDeltaFeedApiWrapper $product_feed_api_wrapper
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->skuInfoHelper = $sku_info_helper;
    $this->renderer = $renderer;
    $this->metaTagManager = $metaTagManager;
    $this->tokenService = $token;
    $this->configFactory = $config_factory;
    $this->connection = $connection;
    $this->logger = $logger;
    $this->dyProductDeltaFeedApiWrapper = $product_feed_api_wrapper;
  }

  /**
   * Get currency code.
   */
  private function getCurrencyCode() {
    if (empty(self::$currencyCode)) {
      $currency_config_ar = $this->languageManager->getLanguageConfigOverride('ar', 'acq_commerce.currency');
      $currency_config = $this->configFactory->get('acq_commerce.currency');
      self::$currencyCode = [
        'ar' => $currency_config_ar->get('currency_code'),
        'en' => $currency_config->get('currency_code'),
      ];
    }

    return self::$currencyCode;
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
  public function prepareProductFeedData(int $nid): array {
    $product = [];
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

    // Disable alshaya_color_split hook calls.
    SkuManager::$colorSplitMergeChildren = FALSE;
    // Disable image download.
    SKU::$downloadImage = FALSE;
    // Disable API calls.
    AlshayaAcmApiWrapper::$invokeApi = FALSE;

    if ($sku->bundle() == 'simple') {
      $product[$sku->getSku()] = $this->getSkuFields($node, $sku);

      return $product;
    }

    if ($sku->bundle() === 'configurable') {
      $combinations = $this->skuManager->getConfigurableCombinations($sku);

      foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
        $child = SKU::loadFromSku($child_sku, $node->language()->getId());
        if (!$child instanceof SKUInterface) {
          continue;
        }

        $product[$child_sku] = $this->getSkuFields($node, $child);
      }

      return $product;
    }

    return [];
  }

  /**
   * Get details of given sku.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $language
   *   Language.
   * @param string $key_prefix
   *   Key prefix.
   *
   * @return array
   *   Return array of sku fields.
   */
  private function getSkuDetails(NodeInterface $node, SKUInterface $sku, $language = '', $key_prefix = ''): array {
    if (!$node instanceof NodeInterface || !$sku instanceof SKUInterface) {
      return [];
    }

    $lang = !empty($language)
      ? $language
      : $node->language()->getId();
    $stockInfo = $this->skuInfoHelper->stockInfo($sku);
    $prices = $this->skuManager->getMinPrices($sku);

    $fields = [];

    // Get currency code.
    $currencyCode = $this->getCurrencyCode();

    // Default fields non market specific.
    $fields['sku'] = $sku->getSku();
    $fields['name'] = $node->label();
    $fields['url'] = $this->skuInfoHelper->getEntityUrl($node);
    $fields['currency'] = $currencyCode[$lang];
    $fields['price'] = $this->getCleanPrice($prices['price']);
    $fields['regular_price'] = $fields['price'];
    $fields['dy_display_price'] = $fields['price'];
    $fields['sale_price'] = $this->getCleanPrice($prices['final_price']);
    $fields['discount'] = $this->skuManager->getDiscountedPercent($prices['price'], $prices['final_price']);
    $fields['group_id'] = $sku->getSku();
    $fields['on_sale'] = $this->getAttributeValue($sku, 'is_sale');
    // Field condition with static value.
    $fields['condition'] = 'new';
    $fields['image_url'] = $this->getFirstImageUrl($sku);
    $fields['in_stock'] = $stockInfo['in_stock'];
    $fields['quantity'] = $stockInfo['stock'];
    $fields['keywords'] = $this->tokenService->replace(
      $this->metaTagManager->tagsFromEntityWithDefaults($node)['keywords'],
      ['node' => $node],
      ['langcode' => $lang],
      new BubbleableMetadata()
    );
    // Get all category fields.
    $this->getFormattedProductCategories($fields, $node, $lang);
    $this->getDyCategories($fields, $node, $lang);
    $description = $this->skuManager->getDescription($sku, 'full');
    $longText = $this->renderer->renderPlain($description)->__toString();
    $fields['description'] = !empty($longText) ? $this->getTruncatedDescription($longText, 1000, '') : '';
    $fields['color'] = $this->getAttributeValue($sku, 'color');
    $fields['size'] = $this->getAttributeValue($sku, 'size');
    $fields['concept'] = $this->getAttributeValue($sku, 'concept');
    $fields['brand'] = $this->getAttributeValue($sku, 'product_brand');
    $fields['is_new'] = $this->getAttributeValue($sku, 'is_new');

    // Locale fields.
    $fields[$key_prefix . 'name'] = $node->label();
    $fields[$key_prefix . 'url'] = $this->skuInfoHelper->getEntityUrl($node);
    $fields[$key_prefix . 'currency'] = $currencyCode[$lang];
    $fields[$key_prefix . 'price'] = $fields['price'];
    $fields[$key_prefix . 'regular_price'] = $fields['regular_price'];
    $fields[$key_prefix . 'sale_price'] = $fields['sale_price'];
    $fields[$key_prefix . 'discount'] = $fields['discount'];
    $fields[$key_prefix . 'quantity'] = $stockInfo['stock'];
    $fields[$key_prefix . 'in_stock'] = $stockInfo['in_stock'];
    $fields[$key_prefix . 'description'] = $fields['description'];
    $fields[$key_prefix . 'category'] = $fields['category'];
    $fields[$key_prefix . 'on_sale'] = $fields['on_sale'];

    // Common locale fields for all brands.
    $fields[$key_prefix . 'color'] = $fields['color'];
    $fields[$key_prefix . 'size'] = $fields['size'];
    $fields[$key_prefix . 'concept'] = $fields['concept'];
    $fields[$key_prefix . 'brand'] = $fields['brand'];
    $fields[$key_prefix . 'is_new'] = $fields['is_new'];

    // @todo CORE-27353 Index promo_link, promo_label and index it separately for APP and WEB.
    $fields['promo_label'] = '';
    $fields[$key_prefix . 'promo_label'] = '';

    return $fields;
  }

  /**
   * Get clean price for DY feed.
   *
   * @param string $price
   *   The price.
   *
   * @return string|string[]
   *   Clean price.
   */
  private function getCleanPrice($price) {
    $formattedPrice = $this->skuInfoHelper->formatPriceDisplay((float) $price);
    return str_replace(',', '', $formattedPrice);
  }

  /**
   * Truncate text.
   *
   * @param string $string
   *   Text string.
   * @param int $length
   *   Length to trim.
   * @param string $append
   *   Ellipses.
   *
   * @return string
   *   Truncate text.
   */
  private function getTruncatedDescription($string, $length = 1000, $append = '&hellip;') {
    $string = strip_tags(trim($string));
    // Remove new line.
    $string = trim(preg_replace('/\s+/', ' ', $string));
    if (strlen($string) > $length) {
      $string = wordwrap($string, $length);
      $string = explode("\n", $string, 2);
      $string = $string[0] . $append;
    }

    return $string;
  }

  /**
   * Get all the fields of given sku.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Return array of all required sku fields.
   */
  private function getSkuFields(NodeInterface $node, SKUInterface $sku): array {
    $fields = [];

    foreach ($this->languageManager->getLanguages() as $lang => $language) {
      $node = $this->skuInfoHelper->getEntityTranslation($node, $lang);

      if ($node->language()->getId() !== $lang) {
        continue;
      }

      $sku = $this->skuInfoHelper->getEntityTranslation($sku, $lang);
      $locale = AlshayaI18nLanguages::getLocale($lang);
      $locale_key_prefix = 'lng:' . $locale . ':';
      // Prepare default fields.
      $default_fields = $this->getSkuDetails($node, $sku, $lang, $locale_key_prefix);

      $fields = array_merge($default_fields, $fields);

      // Adding brand specific fields to the product feed.
      $brand_fields = $this->getBrandsSpecificFields($node, $sku, $locale_key_prefix);
      $fields = array_merge($fields, $brand_fields);
    }

    return $fields;
  }

  /**
   * Gets brand specific fields for feed.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $key_prefix
   *   SKU Entity.
   *
   * @return array
   *   Return array of all brand specific sku fields.
   */
  private function getBrandsSpecificFields(NodeInterface $node, SKUInterface $sku, $key_prefix) {
    $brand_fields = [];
    $fields = $this->configFactory->get('alshaya_feed.settings')->get('brand_fields') ?? [];
    foreach ($fields as $field) {
      switch ($field) {
        case 'short_description':
          $short_desc = $this->skuManager->getShortDescription($sku, 'full');
          $brand_fields['short_description'] = !empty($short_desc['value']) ? $this->renderer->renderPlain($short_desc['value'])->__toString() : '';
          $brand_fields[$key_prefix . 'short_description'] = $brand_fields['short_description'];
          break;

        default:
          $brand_fields[$field] = $this->getAttributeValue($sku, $field);
          $brand_fields[$key_prefix . $field] = $brand_fields[$field];
      }
    }

    return $brand_fields;
  }

  /**
   * Wrapper function get attribute value.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $attribute_key
   *   Attribute Key.
   *
   * @return string
   *   Attribute Value.
   */
  private function getAttributeValue(SKUInterface $sku, string $attribute_key) {
    if (!$sku->hasField('attr_' . $attribute_key)) {
      return '';
    }

    return $sku->get('attr_' . $attribute_key)->getString();
  }

  /**
   * Wrapper function get first image url from gallery images.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return string
   *   Image URL.
   */
  private function getFirstImageUrl(SKUInterface $sku) {
    $media_items = $this->skuImagesManager->getProductMedia($sku, 'pdp');
    if (empty($media_items) || empty($media_items['media_items']) || !is_array($media_items['media_items']['images'])) {
      return [];
    }

    $image_style_plp = ImageStyle::load('product_listing');
    $image_url = $image_style_plp->buildUrl(reset($media_items['media_items']['images'])['drupal_uri']);

    return $image_url;
  }

  /**
   * Get formatted all unique categories associated with node.
   *
   * @param array $fields
   *   Fields list.
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param string|null $lang
   *   The lang code.
   */
  private function getFormattedProductCategories(array &$fields, NodeInterface $node, $lang = NULL) {
    // All category.
    $fields['category'] = '';
    // Categories with excluded terms.
    $fields['categories'] = '';
    // Parent category.
    $fields['parent_category'] = '';

    $categories_collection = $this->skuInfoHelper->getProductCategories($node, $lang);

    if (empty($categories_collection)) {
      return;
    }

    $categories = array_unique(array_column($categories_collection, 'category_hierarchy'));
    $fields['category'] = implode('|', $categories);
    $fields['parent_category'] = !empty($categories) ? reset($categories) : '';

    $categories_to_exclude = $this->configFactory->get('alshaya_feed.settings')->get('categories_to_exclude') ?? [];
    $categories_to_exclude_lowercase = array_map('strtolower', $categories_to_exclude);

    $parsed_categories = array_filter($categories, function ($e) use ($categories_to_exclude_lowercase) {
      foreach ($categories_to_exclude_lowercase as $value) {
        if (str_contains(strtolower($e), $value)) {
            return FALSE;
        }
      }
      return TRUE;
    });

    $fields['categories'] = !empty($parsed_categories) ? implode('|', array_unique($parsed_categories)) : '';
  }

  /**
   * Delta Feed data to make SKU OOS.
   *
   * @param string $sku
   *   The product sku string.
   *
   * @return array
   *   Data array.
   */
  public function prepareFeedDataforSkuOos(string $sku) {
    $fields = [];
    $fields['sku'] = $sku;
    foreach ($this->languageManager->getLanguages() as $lang => $language) {
      $locale_key_prefix = 'lng:' . AlshayaI18nLanguages::getLocale($lang) . ':';
      $fields[$locale_key_prefix . 'in_stock'] = FALSE;
      $fields[$locale_key_prefix . 'quantity'] = 0;
    }

    return $fields;
  }

  /**
   * Wrapper function get DY categories.
   *
   * @param array $fields
   *   Fields list.
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param string|null $lang
   *   The lang code.
   */
  private function getDyCategories(array &$fields, NodeInterface $node, $lang = NULL) {
    $number_of_dy_categories = 3;
    $categories = $node->get('field_category')->referencedEntities();

    // Return if no categories attached to product.
    if (empty($categories)) {
      $fields['dy_categories'] = '';
      return $fields;
    }

    $dy_categories = [];
    $extra_dy_categories = [];

    foreach ($categories as $term) {
      $term = $this->skuInfoHelper->getEntityTranslation($term, $lang);

      // Skip if category is not enabled.
      if (!$term->get('field_commerce_status')->getString()) {
        continue;
      }

      $dy_category_level = $term->get('field_dy_category')->getString();

      // Skip if dy_category field is empty or NONE.
      if (empty($dy_category_level) || $dy_category_level === 'NONE') {
        continue;
      }

      // Pick one category for each level and the rest as extra.
      if (empty($dy_categories[$dy_category_level])) {
        $dy_categories[$dy_category_level] = $term->label();
      }
      else {
        $extra_dy_categories[] = $term->label();
      }
    }

    // Return if no dy_categories attached to product.
    if (empty($dy_categories)) {
      $fields['dy_categories'] = '';
      return $fields;
    }

    // Sort dy categories as level L1,L2,L3.
    ksort($dy_categories);

    $size_of_dy_categories = count($dy_categories);

    // If dy_categories array size is less than required
    // number_of_dy_categories then add the remaining categories
    // from extra_dy_categories array if present.
    if ($size_of_dy_categories < $number_of_dy_categories && !empty($extra_dy_categories)) {
      $dy_categories = array_merge(
        $dy_categories,
        array_slice($extra_dy_categories, 0, $number_of_dy_categories - $size_of_dy_categories)
      );
    }

    $fields['dy_categories'] = implode('|', $dy_categories);

    return $fields;
  }

  /**
   * Insert SKU in table which stores OOS product SKUs.
   *
   * @param string $sku
   *   The product sku string.
   */
  public function saveOosProductSku(string $sku) {
    try {
      $insert = $this->connection->insert(self::OOS_SKU_TABLE_NAME);
      $insert->fields(['sku']);
      $insert->values([
        'sku' => $sku,
      ]);
      $insert->execute();
    }
    catch (IntegrityConstraintViolationException $e) {
      // Do nothing, SKU already present in the table.
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while inserting sku @sku in table `oos_product_skus`, message: @message', [
        '@sku' => $sku,
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Get list of OOS product SKUs.
   *
   * @return array
   *   The list of skus.
   */
  public function getOosProductSkus() {
    $query = $this->connection->select(self::OOS_SKU_TABLE_NAME);
    $query->fields(self::OOS_SKU_TABLE_NAME, ['sku']);
    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Delete SKU from table which stores OOS product SKUs.
   *
   * @param string $sku
   *   The product sku string to delete.
   */
  public function deleteOosProductSku(string $sku) {
    $this->connection->delete(self::OOS_SKU_TABLE_NAME)
      ->condition('sku', $sku)
      ->execute();
  }

  /**
   * Delete SKU from feeds.
   *
   * @param string $sku
   *   SKU.
   */
  public function deleteFromFeed(string $sku) {
    $feeds = $this->configFactory->get('dynamic_yield.settings')->get('feeds');

    if (empty($feeds)) {
      $this->logger->notice('DY Feeds config is empty - dynamic_yield.settings:feeds.');
      return;
    }

    foreach ($feeds as $feed) {
      $this->dyProductDeltaFeedApiWrapper->productFeedDelete($feed['api_key'], $feed['id'], $sku);
    }

    $this->logger->notice('DY delete API invoked. Processed product with sku: @sku.', [
      '@sku' => $sku,
    ]);
  }

}
