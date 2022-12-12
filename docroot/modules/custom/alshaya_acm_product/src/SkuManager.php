<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\CartFormHelper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\alshaya_acm_product\Service\ProductProcessedManager;
use Drupal\alshaya_config\AlshayaArrayUtils;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Site\Settings;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\pathauto\PathautoState;
use Drupal\search_api\Item\ItemInterface;
use Drupal\simple_sitemap\Simplesitemap;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityInterface;
use Drupal\taxonomy\TermInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\Plugin\rest\resource\StockResource;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Class Sku Manager.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuManager {

  use StringTranslationTrait;

  public const FREE_GIFT_PRICE = 0.01;

  public const PDP_LAYOUT_INHERIT_KEY = 'inherit';

  public const PDP_LAYOUT_MAGAZINE = 'pdp-magazine';

  public const PDP_LAYOUT_MAGAZINE_V2 = 'pdp-magazine_v2';

  public const AGGREGATED_LISTING = 'aggregated';

  public const NON_AGGREGATED_LISTING = 'non_aggregated';

  public const FREE_GIFT_SUB_TYPE_ALL_SKUS = 0;

  public const FREE_GIFT_SUB_TYPE_ONE_SKU = 1;

  public const SIZE_GROUP_SEPARATOR = '|';

  /**
   * Flag to allow merge children in alshaya_color_split.
   *
   * @var bool
   */
  public static $colorSplitMergeChildren = TRUE;

  /**
   * Store selected variant id.
   *
   * @var int
   */
  private static $selectedVariantId;

  /**
   * The database service.
   *
   * @var \Drupal\mysql\Driver\Database\mysql\Connection
   */
  protected $connection;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Linked SKUs service.
   *
   * @var \Drupal\acq_sku\AcqSkuLinkedSku
   */
  protected $linkedSkus;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Cache Backend service for product labels.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $productLabelsCache;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current Route object.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRoute;

  /**
   * Node storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * File storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * SKU storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $skuStorage;

  /**
   * SKU storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Cart Form helper service.
   *
   * @var \Drupal\acq_sku\CartFormHelper
   */
  protected $cartFormHelper;

  /**
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * Product Category Helper service object.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  protected $productCategoryHelper;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Simple sitemap generator.
   *
   * @var \Drupal\simple_sitemap\Simplesitemap
   */
  protected $generator;

  /**
   * Product Info Helper.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  protected $productInfoHelper;
  /**
   * Alshaya array utils service.
   *
   * @var \Drupal\alshaya_config\AlshayaArrayUtils
   */
  protected $alshayaArrayUtils;

  /**
   * Product Cache Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductCacheManager
   */
  protected $productCacheManager;

  /**
   * Product Processed Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductProcessedManager
   */
  protected $productProcessedManager;

  /**
   * Alshaya Request Context Manager.
   *
   * @var \Drupal\alshaya_acm_product\AlshayaRequestContextManager
   */
  protected $requestContextManager;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Current time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $currentTime;

  /**
   * SkuManager constructor.
   *
   * @param \Drupal\mysql\Driver\Database\mysql\Connection $connection
   *   Database service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route
   *   Current Route object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The lnaguage manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger service.
   * @param \Drupal\acq_sku\AcqSkuLinkedSku $linked_skus
   *   Linked SKUs service.
   * @param \Drupal\acq_sku\CartFormHelper $cart_form_helper
   *   Cart Form helper service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Cache\CacheBackendInterface $product_labels_cache
   *   Cache Backend service for product labels.
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   * @param \Drupal\alshaya_acm_product\ProductCategoryHelper $product_category_helper
   *   Product Category Helper service object.
   * @param \GuzzleHttp\Client $http_client
   *   GuzzleHttp\Client object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer service.
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   *   Simple sitemap generator.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info Helper.
   * @param \Drupal\alshaya_acm_product\Service\ProductCacheManager $product_cache_manager
   *   Product Cache Manager.
   * @param \Drupal\alshaya_config\AlshayaArrayUtils $alshayaArrayUtils
   *   Alshaya array utility service.
   * @param \Drupal\alshaya_acm_product\Service\ProductProcessedManager $product_processed_manager
   *   Product Processed Manager.
   * @param \Drupal\alshaya_acm_product\AlshayaRequestContextManager $alshayaRequestContextManager
   *   Alshaya Request Context Manager.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The filesystem service.
   * @param \Drupal\Component\Datetime\TimeInterface $current_time
   *   Current time service.
   */
  public function __construct(Connection $connection,
                              ConfigFactoryInterface $config_factory,
                              CurrentRouteMatch $current_route,
                              RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              LoggerChannelFactoryInterface $logger_factory,
                              AcqSkuLinkedSku $linked_skus,
                              CartFormHelper $cart_form_helper,
                              ModuleHandlerInterface $module_handler,
                              CacheBackendInterface $cache,
                              CacheBackendInterface $product_labels_cache,
                              SKUFieldsManager $sku_fields_manager,
                              ProductCategoryHelper $product_category_helper,
                              Client $http_client,
                              RendererInterface $renderer,
                              Simplesitemap $generator,
                              ProductInfoHelper $product_info_helper,
                              ProductCacheManager $product_cache_manager,
                              AlshayaArrayUtils $alshayaArrayUtils,
                              ProductProcessedManager $product_processed_manager,
                              AlshayaRequestContextManager $alshayaRequestContextManager,
                              FileSystemInterface $fileSystem,
                              TimeInterface $current_time) {
    $this->connection = $connection;
    $this->configFactory = $config_factory;
    $this->currentRoute = $current_route;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->skuStorage = $entity_type_manager->getStorage('acq_sku');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->logger = $logger_factory->get('alshaya_acm_product');
    $this->linkedSkus = $linked_skus;
    $this->cartFormHelper = $cart_form_helper;
    $this->moduleHandler = $module_handler;
    $this->cache = $cache;
    $this->productLabelsCache = $product_labels_cache;
    $this->skuFieldsManager = $sku_fields_manager;
    $this->productCategoryHelper = $product_category_helper;
    $this->httpClient = $http_client;
    $this->renderer = $renderer;
    $this->generator = $generator;
    $this->productInfoHelper = $product_info_helper;
    $this->productCacheManager = $product_cache_manager;
    $this->alshayaArrayUtils = $alshayaArrayUtils;
    $this->productProcessedManager = $product_processed_manager;
    $this->requestContextManager = $alshayaRequestContextManager;
    $this->fileSystem = $fileSystem;
    $this->currentTime = $current_time;
  }

  /**
   * Get SKU object from id in current language.
   *
   * @param int $id
   *   SKU Entity ID.
   *
   * @return \Drupal\acq_commerce\SKUInterface|null
   *   Loaded SKU object in current language.
   */
  public function loadSkuById(int $id) : ?SKUInterface {
    if ($id <= 0) {
      // Return null for 0 or negative values.
      // 0 is possible, negative - not sure.
      return NULL;
    }

    $skus = &drupal_static('loadSkuById', []);
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    if (isset($skus[$id], $skus[$id][$langcode])) {
      return $skus[$id][$langcode];
    }

    $sku = SKU::load($id);
    if ($sku instanceof SKUInterface) {
      if ($sku->language()->getId() !== $langcode && $sku->hasTranslation($langcode)) {
        $sku = $sku->getTranslation($langcode);
      }

      $skus[$id][$sku->language()->getId()] = $sku;
      return $sku;
    }

    return NULL;
  }

  /**
   * Get Image tag with optional rel.
   *
   * @param string $uri
   *   URI of file.
   * @param string $alt
   *   Image alternate text.
   * @param string $image_style
   *   Image style to apply to the image.
   * @param string $rel_image_style
   *   For some sliders we may want full/big image url in rel.
   *
   * @return array
   *   Image build array.
   */
  public function getSkuImage(string $uri,
                              string $alt = '',
                              string $image_style = '',
                              string $rel_image_style = '') {
    $image = [
      '#theme' => 'image_style',
      '#style_name' => $image_style,
      '#uri' => $uri,
      '#title' => $alt,
      '#alt' => $alt,
    ];

    if ($rel_image_style) {
      $image['#attributes']['rel'] = ImageStyle::load($rel_image_style)->buildUrl($image['#uri']);
    }

    return $image;
  }

  /**
   * Get Discounted Price markup.
   *
   * @param float|string $price
   *   Price value.
   * @param float|string $final_price
   *   Final price value.
   * @param string $langcode
   *   Language code used.
   *
   * @return string
   *   Price markup.
   */
  public function getDiscountedPriceMarkup($price, $final_price, $langcode = ''):string {
    $discount = $this->getDiscountedPercent($price, $final_price);

    if ($discount > 0) {
      $options = $langcode ? ['langcode' => $langcode] : [];

      return (string) $this->t('Save @discount%', ['@discount' => $discount], $options);
    }

    return '';
  }

  /**
   * Wrapper function to get discount percentage.
   *
   * @param float|string $price
   *   Original price.
   * @param float|string $final_price
   *   Final price.
   *
   * @return float
   *   Discount percentage.
   */
  public function getDiscountedPercent($price, $final_price):float {
    $price = (float) $price;
    $final_price = (float) $final_price;

    $discount = $price - $final_price;
    if ($price < 0.1 || $final_price < 0.1 || $discount < 0.1) {
      return 0;
    }

    return (float) round(($discount * 100) / $price);
  }

  /**
   * Get minimum final price and associated initial price for configurable.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU Entity.
   * @param string $color
   *   Color value to limit the scope of skus to get price.
   *
   * @return array
   *   Minimum final price and associated initial price.
   */
  public function getMinPrices(SKU $sku_entity, string $color = '') {
    $cache_key = implode(':', array_filter(['product_price', $color]));
    $cache = $this->productCacheManager->get($sku_entity, $cache_key);

    // Do not process the same thing again and again.
    if (is_array($cache)) {
      return $cache;
    }

    $prices = [
      'price' => 0,
      'final_price' => 0,
      'fixed_price' => 0,
    ];
    if ($sku_entity->bundle() == 'simple') {
      $price = (float) acq_commerce_get_clean_price($sku_entity->get('price')->getString());
      $final_price = (float) acq_commerce_get_clean_price($sku_entity->get('final_price')->getString());

      // Set fixed price for XB.
      $fixed_price = $sku_entity->hasField('attr_fixed_price')
        ? $sku_entity->get('attr_fixed_price')->getString()
        : NULL;

      if ((empty($price) && $final_price > 0) || ($final_price >= $price)) {
        $price = $final_price;
      }
      elseif (empty($final_price)) {
        $final_price = $price;
      }

      $prices = [
        'price' => $price,
        'final_price' => $final_price,
        // Add fixed price rendered in data attribute for XB.
        'fixed_price' => $fixed_price,
      ];
      $this->productCacheManager->set($sku_entity, $cache_key, $prices);
      return $prices;
    }

    if ($color) {
      $combinations = $this->getConfigurableCombinations($sku_entity);
      foreach ($this->getPdpSwatchAttributes() as $attribute_code) {
        if (isset($combinations['attribute_sku'][$attribute_code])) {
          $children = $combinations['attribute_sku'][$attribute_code][$color];
          break;
        }
      }
    }
    else {
      $children = $this->getValidChildSkusAsString($sku_entity);
    }
    $sku_price = 0;

    foreach ($children ?? [] as $child_sku_code) {
      try {
        $child_sku_entity = SKU::loadFromSku($child_sku_code, $sku_entity->language()->getId());

        if ($child_sku_entity instanceof SKU) {
          $prices['children'][$child_sku_code] = $this->getMinPrices($child_sku_entity);
          $price = $prices['children'][$child_sku_code]['price'];

          // Get fixed price from child SKU.
          $fixed_price = $prices['children'][$child_sku_code]['fixed_price'] ?? NULL;

          $final_price = $prices['children'][$child_sku_code]['final_price'];
          if ($prices['children'][$child_sku_code]['final_price'] == $price) {
            $prices['children'][$child_sku_code]['final_price'] = 0;
          }
          $prices['children'][$child_sku_code]['selling_price'] = min($price, $final_price);
          $prices['children'][$child_sku_code]['discount'] = $this->getDiscountedPercent($price, $final_price);
          $prices['children'][$child_sku_code]['fixed_price'] = $fixed_price;

          $new_sku_price = 0;
          if ($final_price > 0) {
            $new_sku_price = $sku_price > 0 ? min($sku_price, $final_price) : $final_price;
          }
          elseif ($price > 0) {
            $new_sku_price = $sku_price > 0 ? min($sku_price, $price) : $price;
          }

          // Do we need to update selected prices?
          if ($new_sku_price != 0) {
            // Have we found a new min final price?
            if ($sku_price != $new_sku_price) {
              $sku_price = $new_sku_price;
              $prices['price'] = $price;
              $prices['final_price'] = $final_price;

              // Pass fixed price data to render in data attribute for XB.
              $prices['fixed_price'] = $fixed_price;
            }
            // Is the difference between initial an final bigger?
            elseif ($price != 0
              && $final_price != 0
              && $prices['price'] != 0
              && $prices['final_price'] != 0
              && ($price - $final_price) > ($prices['price'] - $prices['final_price'])
            ) {
              $prices['price'] = $price;
              $prices['final_price'] = $final_price;

              // Pass fixed price data to render in data attribute for XB.
              $prices['fixed_price'] = $fixed_price;
            }
          }
        }
      }
      catch (\Exception) {
        // Child SKU might be deleted or translation not available.
        // Log messages are already set in previous functions.
      }
    }

    $diff_multiplier = Settings::get('alshaya_debug_parent_price_huge_diff_multiplier', 5);
    // Log only if product not free gift and multiplier set.
    if ($diff_multiplier && $prices['final_price'] > self::FREE_GIFT_PRICE) {
      $final_price_parent = (float) acq_commerce_get_clean_price($sku_entity->get('final_price')->getString());

      // Log the price diff if it is huge (there may be minor variations as
      // we might not show all the products available in back-end system
      // because of it being out of stock or missing images).
      // We try to capture here the cases where price in parent looks like from
      // different market (400 instead of 4 for instance).
      // We log here if it is X times higher or X times lower.
      // By default X is 5 so examples:
      // Log if parent final price is 101 and calculated final price is 20,
      // Log if parent final price is 19 and calculated final price is 100.
      if ($final_price_parent > self::FREE_GIFT_PRICE
        && (($final_price_parent > ($prices['final_price'] * $diff_multiplier))
        || ($final_price_parent < ($prices['final_price'] / $diff_multiplier)))) {
        $this->logger->info('Suspicious price for parent product for sku: @sku, final_price_parent: @final_price_parent, final_price_calculated: @final_price_calculated.', [
          '@sku' => $sku_entity->getSku(),
          '@final_price_parent' => $final_price_parent,
          '@final_price_calculated' => $prices['final_price'],
        ]);
      }
    }

    $this->productCacheManager->set($sku_entity, $cache_key, $prices);
    return $prices;
  }

  /**
   * Wrapper function to get vat text from config.
   *
   * @return string|null
   *   Vat text from config.
   */
  public function getVatText() {
    static $vat_text = NULL;

    if (!isset($vat_text)) {
      $vat_text = $this->configFactory
        ->get('alshaya_acm_product.settings')
        ->get('vat_text');
    }

    return $vat_text;
  }

  /**
   * Helper function to build discounted price for Sku in cart.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku entity from cart for which discount needs to be calculated.
   * @param float $item_price
   *   Unit price for the sku item in the cart.
   *
   * @return mixed
   *   Calculated cart item price.
   */
  public function buildCartItemPrice(SKU $sku_entity, $item_price) {
    $sku_cart_price = [];
    $sku_cart_price['price'] = (float) $sku_entity->get('price')->getString();
    $final_price = (float) $item_price;

    if ($final_price !== $sku_cart_price['price']) {
      if ($final_price > $sku_cart_price['price']) {
        // There must be something wrong. Trust the price coming from commerce
        // backend.
        $sku_cart_price['price'] = $final_price;
        $this->logger->error(
          'The @sku sku has a final price greater than the initial price. There must be a synchronisation issue.',
          ['@sku' => $sku_entity->sku->value]
        );
      }
      else {
        $sku_cart_price['final_price'] = _alshaya_acm_format_price_with_decimal($final_price);
        $discount = round((($sku_cart_price['price'] - $final_price) * 100) / $sku_cart_price['price']);
        $sku_cart_price['discount']['prefix'] = $this->t('Save', [], ['context' => 'discount']);
        $sku_cart_price['discount']['value'] = $discount . '%';
      }
    }

    return $sku_cart_price;
  }

  /**
   * Helper function to fetch sku from entity id rather than loading the SKU.
   *
   * @param array $sku_entity_ids
   *   Entity id of the Sku item.
   *
   * @return array
   *   Array of Sku Ids of the item.
   */
  public function getSkusByEntityId(array $sku_entity_ids) {
    if (empty($sku_entity_ids)) {
      return [];
    }

    $query = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['sku'])
      ->distinct()
      ->condition('id', $sku_entity_ids, 'IN');

    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Helper function to fetch entity id from sku rather than loading the SKU.
   *
   * @param array $sku_texts
   *   Sku text of the Sku item.
   *
   * @return array
   *   Array of Entity Ids of sku items.
   *
   * @throws \Drupal\Core\Database\InvalidQueryException
   */
  public function getEntityIdsBySku(array $sku_texts) {
    if (empty($sku_texts)) {
      return [];
    }

    $query = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['id'])
      ->distinct()
      ->condition('sku', $sku_texts, 'IN');

    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Helper function to fetch child skus of a configurable Sku.
   *
   * @param mixed $sku
   *   sku text or Sku object.
   * @param bool $first_only
   *   Boolean flag to indicate if we want to load only the first child.
   *
   * @return \Drupal\acq_sku\Entity\SKU[]|\Drupal\acq_sku\Entity\SKU
   *   Array of child skus/ Child SKU when loading first child only.
   */
  public function getChildSkus($sku, $first_only = FALSE) {
    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);
    $child_skus = [];

    if ($sku_entity->getType() != 'configurable') {
      return $child_skus;
    }
    foreach (Configurable::getChildSkus($sku_entity) as $child_sku) {
      try {
        $child_sku_entity = SKU::loadFromSku($child_sku, $sku_entity->language()->getId());

        if (!($child_sku_entity instanceof SKU) || $this->isSkuFreeGift($child_sku_entity)) {
          continue;
        }

        // Return the first valid SKU if only one is required.
        if ($first_only) {
          return $child_sku_entity;
        }

        $child_skus[] = $child_sku_entity;
      }
      catch (\Exception) {
        continue;
      }
    }

    return $child_skus;
  }

  /**
   * Get all available children for configurable product.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   Parent sku to get children of.
   * @param bool $first_only
   *   Flag to specify only first is required or all.
   *
   * @return \Drupal\acq_sku\Entity\SKU|\Drupal\acq_sku\Entity\SKU[]|null
   *   First sku if first_only true or array of skus.
   */
  public function getAvailableChildren(SKUInterface $sku, $first_only = FALSE) {
    $childSkus = [];

    $langcode = $sku->language()->getId();
    $combinations = $this->getConfigurableCombinations($sku);
    foreach ($combinations['attribute_sku'] ?? [] as $children) {
      foreach ($children as $child_skus) {
        foreach ($child_skus as $child_sku) {
          $child = SKU::loadFromSku($child_sku, $langcode);

          if (!($child instanceof SKUInterface)) {
            continue;
          }

          if ($first_only) {
            return $child;
          }

          $childSkus[$child->getSku()] = $child;
        }
      }
    }

    return $childSkus;
  }

  /**
   * Get SKU based on attribute option id.
   *
   * @param \Drupal\acq_sku\Entity\SKU $parent_sku
   *   Parent Sku.
   * @param string $attribute
   *   Attribute to search for.
   * @param int $option_id
   *   Option id for selected attribute.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   SKU object matching the attribute option id.
   */
  public function getChildSkuFromAttribute(SKU $parent_sku, $attribute, $option_id) {
    $combinations = $this->getConfigurableCombinations($parent_sku);
    // If combination not available.
    if (empty($combinations['attribute_sku'][$attribute][$option_id])) {
      $this->logger->warning('No combination available for attribute @attribute and option @option for SKU @sku', [
        '@attribute' => $attribute,
        '@option' => $option_id,
        '@sku' => $parent_sku->getSku(),
      ]);
      return NULL;
    }

    $sku = reset($combinations['attribute_sku'][$attribute][$option_id]);
    return SKU::loadFromSku($sku);
  }

  /**
   * Get Promotions data for provided SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Array of promotions data.
   */
  public function getPromotionsForSearchViewFromSkuId(SKUInterface $sku): array {
    $cache_key = 'promotions_for_search_view';
    $promos = $this->productCacheManager->get($sku, $cache_key);

    if (!is_array($promos)) {
      // Use 'all' for context to get promotions for Algolia / Search.
      $promos = $this->getPromotionsFromSkuId($sku, 'default', ['cart'], NULL, NULL, 'all');
      $tags = [];
      foreach (array_keys($promos) as $id) {
        $tags[] = 'node:' . $id;
      }
      $this->productCacheManager->set($sku, $cache_key, $promos, $tags);
    }

    return $promos ?? [];
  }

  /**
   * Fetch SKU Promotions with field_acq_promotion_label value.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Product SKU.
   * @param array $types
   *   Promotion Types.
   * @param string $context
   *   Promotion context.
   *
   * @return array
   *   List of Promotion Nids.
   */
  public function getSkuPromotions(SKU $sku, array $types = [
    'cart',
    'category',
  ], $context = '') {
    $promotion_cache_tags = [];
    // Get promotions for the product.
    $cache_key = 'promotion_ids_' . implode('-', $types) . '_' . $context;
    $promotion_nids = $this->productCacheManager->get($sku, $cache_key);

    if (is_array($promotion_nids)) {
      return $promotion_nids;
    }
    $skus = [$sku->getSku()];

    if ($sku->bundle() == 'simple') {
      /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
      $plugin = $sku->getPluginInstance();
      $parent = $plugin->getParentSku($sku);
      if ($parent instanceof SKUInterface) {
        $skus[] = $parent->getSku();
      }
    }

    // Get all the promotions for the product.
    $promotion_nids = $this->fetchPromotionBySkus($skus);
    $full_catalog_promo_nids = $this->fetchFullCatalogPromotion();
    $promotion_nids = array_merge($promotion_nids, $full_catalog_promo_nids);

    if (!empty($promotion_nids)) {
      $promotion_nids = array_unique($promotion_nids);

      $query = $this->nodeStorage->getQuery();
      $query->condition('nid', $promotion_nids, 'IN');
      $query->condition('field_acq_promotion_type', $types, 'IN');
      $query->condition('status', NodeInterface::PUBLISHED);

      // Use 'all' for context to get promotions for both web and app.
      if (!empty($context) && $context !== 'all') {
        $query->condition('field_acq_promotion_context', $context);
      }

      $query->exists('field_acq_promotion_label');
      $promotion_nids = $query->execute();
    }
    $promotion_cache_tags[] = 'node_type:acq_promotion';
    $this->productCacheManager->set($sku, $cache_key, $promotion_nids, $promotion_cache_tags);
    return $promotion_nids;
  }

  /**
   * Fetch promotion applicable to an array of skus.
   *
   * @param array $skus
   *   Product SKU.
   *
   * @return array
   *   List of promotion node ids.
   */
  protected function fetchPromotionBySkus(array $skus) {
    $query = $this->connection->select('node__field_acq_promotion_rule_id', 'node_field');
    $query->fields('node_field', ['entity_id']);
    $query->join('acq_sku_promotion', 'mapping', 'mapping.rule_id = node_field.field_acq_promotion_rule_id_value');
    $query->condition('mapping.sku', $skus, 'IN');
    return $query->execute()->fetchCol();
  }

  /**
   * Fetch promotion applicable to entire catalog.
   *
   * @return array
   *   List of promotion node ids.
   */
  protected function fetchFullCatalogPromotion() {
    if ($full_catalog_promo_nids = $this->cache->get('full_catalog_promo_nids_list')) {
      return $full_catalog_promo_nids->data;
    }
    // Fetch promotion nodes which apply to the entire catalog of products.
    $query = $this->connection->select('node', 'n');
    $query->join('node__field_acq_promotion_full_catalog', 'full_catalog', 'full_catalog.entity_id = n.nid');
    $query->condition('full_catalog.field_acq_promotion_full_catalog_value', 1);
    $query->fields('n', ['nid']);
    $query->distinct();
    $full_catalog_promo_nids = $query->execute()->fetchCol();

    $promotion_cache_tags = array_map(fn($nid) => "node:$nid", $full_catalog_promo_nids);

    // Adding list cache tag considering addition/deletion of promotion
    // nodes.
    $promotion_cache_tags[] = 'node_type:acq_promotion';
    $this->cache->set('full_catalog_promo_nids_list', $full_catalog_promo_nids, CacheBackendInterface::CACHE_PERMANENT, $promotion_cache_tags);
    return $full_catalog_promo_nids;
  }

  /**
   * Prepare display for Promotions of a SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The SKU Entity, for which linked promotions need to be fetched.
   * @param array|\Drupal\Core\Entity\EntityInterface[] $promotion_nodes
   *   List of promotion nodes.
   * @param string $view_mode
   *   View mode around how the promotion needs to be rendered.
   * @param array $types
   *   Type of promotion to filter on.
   * @param string $product_view_mode
   *   Product view mode for which promotion is being rendered.
   * @param bool $check_parent
   *   Flag to specify if we should check parent sku or not.
   * @param string $context
   *   Promotion context.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   blank array, if no promotions found, else Array of promotion entities.
   */
  public function preparePromotionsDisplay(SKU $sku,
                                           array $promotion_nodes,
                                           $view_mode,
                                           array $types = ['cart', 'category'],
                                           $product_view_mode = NULL,
                                           $check_parent = TRUE,
                                           $context = '') {
    $promos = [];
    $view_mode_original = $view_mode;

    foreach ($promotion_nodes as $promotion_node) {
      if (is_numeric($promotion_node)) {
        $promotion_node = $this->nodeStorage->load($promotion_node);
      }

      if (!($promotion_node instanceof NodeInterface)) {
        continue;
      }

      // Get the promotion with language fallback, if it did not have a
      // translation for $langcode.
      $promotion_node = $this->entityRepository->getTranslationFromContext($promotion_node, $sku->language()->getId());
      $promotion_text = $promotion_node->get('field_acq_promotion_label')->getString();

      $description = '';
      $description_item = $promotion_node->get('field_acq_promotion_description')->first();
      if ($description_item) {
        $description = $description_item->getValue();
      }

      $discount_type = $promotion_node->get('field_acq_promotion_disc_type')->getString();
      $discount_value = $promotion_node->get('field_acq_promotion_discount')->getString();
      $free_gift_skus = [];

      // Alter view mode while rendering a promotion with free skus on PDP.
      if (($product_view_mode == 'full') && !empty($free_gift_skus = $promotion_node->get('field_free_gift_skus')->getValue())) {
        $view_mode = 'free_gift';
      }
      else {
        $view_mode = $view_mode_original;
      }

      switch ($view_mode) {
        case 'links':
          try {
            $promos[$promotion_node->id()] = $promotion_node
              ->toLink($promotion_text)
              ->toString()
              ->getGeneratedLink();
          }
          catch (\Exception $exception) {
            watchdog_exception('alshaya_acm_promotion', $exception);
          }
          break;

        case 'free_gift':
          $promos[$promotion_node->id()] = [];
          $promos[$promotion_node->id()]['type'] = 'free_gift';
          $promos[$promotion_node->id()]['text'] = $promotion_text;
          $promos[$promotion_node->id()]['description'] = $description;
          $promos[$promotion_node->id()]['coupon_code'] = $promotion_node->get('field_coupon_code')->getValue();
          foreach ($free_gift_skus as $free_gift_sku) {
            $promos[$promotion_node->id()]['skus'][] = $free_gift_sku;
          }
          // phpcs:ignore
          $data = unserialize($promotion_node->get('field_acq_promotion_data')->getString());
          $promos[$promotion_node->id()]['promo_type'] = $data['extension']['promo_type'] ?? self::FREE_GIFT_SUB_TYPE_ALL_SKUS;
          foreach ($data['condition']['conditions'][0]['conditions'] ?? [] as $condition) {
            if ($condition['attribute'] === 'quote_item_qty') {
              $promos[$promotion_node->id()]['condition_value'] = $condition['value'];
            }
          }
          break;

        default:
          $promos[$promotion_node->id()] = [
            'type' => 'generic',
            'text' => $promotion_text,
            'description' => $description,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'rule_id' => $promotion_node->get('field_acq_promotion_rule_id')->getString(),
          ];

          if (!empty($free_gift_skus = $promotion_node->get('field_free_gift_skus')->getValue())) {
            $promos[$promotion_node->id()]['skus'] = $free_gift_skus;
          }

          if (!empty($coupon_code = $promotion_node->get('field_coupon_code')->getValue())) {
            $promos[$promotion_node->id()]['coupon_code'] = $coupon_code;
          }
          // phpcs:ignore
          $data = unserialize($promotion_node->get('field_acq_promotion_data')->getString());
          $promos[$promotion_node->id()]['promo_type'] = $data['extension']['promo_type'] ?? self::FREE_GIFT_SUB_TYPE_ALL_SKUS;
          $promotion_context = $promotion_node->get('field_acq_promotion_context')->getValue();
          $promos[$promotion_node->id()]['context'] = ['web', 'app'];
          if (!empty($promotion_context)) {
            $promos[$promotion_node->id()]['context'] = array_column($promotion_context, 'value');
          }
          break;
      }
    }

    // For configurable products there are many rules like rules on product
    // category that get applied to child SKUs even if they don't have the
    // category but parent SKU has the category.
    // To avoid issues in display we check for parent SKU promotions if current
    // SKU (child) has no promotions attached.
    // This is done here to reduce processing in Magento, current process
    // (indexer) in Magento is already heavy and requires enhancement, so
    // it is done in Drupal to avoid more performance issues Magento.
    if (empty($promos) && $check_parent) {
      if ($parentSku = $this->getParentSkuBySku($sku)) {
        if ($parentSku->getSku() != $sku->getSku()) {
          return $this->getPromotionsFromSkuId($parentSku, $view_mode, $types, $product_view_mode, FALSE, $context);
        }
      }
    }

    return $promos;
  }

  /**
   * Get Promotion node object(s) related to provided SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The SKU Entity, for which linked promotions need to be fetched.
   * @param string $view_mode
   *   View mode around how the promotion needs to be rendered.
   * @param array $types
   *   Type of promotion to filter on.
   * @param string $product_view_mode
   *   Product view mode for which promotion is being rendered.
   * @param bool $check_parent
   *   Flag to specify if we should check parent sku or not.
   * @param string $context
   *   Promotion context.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   blank array, if no promotions found, else Array of promotion entities.
   */
  public function getPromotionsFromSkuId(SKU $sku,
                                         $view_mode,
                                         array $types = ['cart', 'category'],
                                         $product_view_mode = NULL,
                                         $check_parent = TRUE,
                                         $context = '') {
    $promos = [];
    if (empty($context)) {
      $context = $this->requestContextManager->getContext();
    }
    $promotion_nodes = $this->getSkuPromotions($sku, $types, $context);
    if (!empty($promotion_nodes)) {
      $promos = $this->preparePromotionsDisplay($sku, $promotion_nodes, $view_mode, $types, $product_view_mode, $check_parent);
    }

    return $promos;
  }

  /**
   * Function to return labels files for a SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku Entity.
   * @param string $type
   *   Type of image required - plp or pdp.
   * @param bool $reset
   *   Flag to reset cache and generate array again from serialized string.
   *
   * @return array
   *   Array of media files.
   */
  public function getLabelsData(SKU $sku_entity, $type = 'plp', $reset = FALSE) {
    static $static_labels_cache = [];
    $langcode = $sku_entity->language()->getId();
    $sku = $sku_entity->getSku();

    if (!$reset && !empty($static_labels_cache[$sku][$langcode][$type])) {
      return $static_labels_cache[$sku][$langcode][$type];
    }

    $static_labels_cache[$sku][$langcode][$type] = [];

    $labels_data = $this->getSkuLabel($sku_entity);

    if (empty($labels_data)) {
      return [];
    }
    else {
      $image_key = $type . '_image';
      $text_key = $type . '_image_text';
      $position_key = $type . '_position';

      foreach ($labels_data as &$data) {
        $row = [];

        // Check if label is available for desired type.
        if (empty($data[$image_key])) {
          continue;
        }

        // Check if label is currently active.
        $from = strtotime($data['from'] ?? '');
        $to = strtotime($data['to'] ?? '');

        // First check if we have date filter.
        if ($from > 0 && $to > 0) {
          $now = $this->currentTime->getRequestTime();

          // Now, check if current date lies between from and to dates.
          if ($from > $now || $to < $now) {
            continue;
          }
        }

        $fid = $this->productLabelsCache->get($data[$image_key]);

        if (empty($fid)) {
          try {
            // Prepare the File object when we access it the first time.
            $fid = $this->downloadLabelsImage($sku_entity, $data, $image_key);
            $this->productLabelsCache->set($data[$image_key], $fid, CacheBackendInterface::CACHE_PERMANENT);
          }
          catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            continue;
          }
        }
        else {
          $fid = $fid->data;
        }

        $image_file = $this->fileStorage->load($fid);
        $uri = $image_file->getFileUri();

        $row['image'] = [
          'uri' => $uri,
          'url' => file_create_url($uri),
          'title' => $data[$text_key],
          'alt' => $data[$text_key],
        ];
        $row['position'] = $data[$position_key];

        $static_labels_cache[$sku][$langcode][$type][] = $row;

        // Disable subsequent images if flag is true.
        if ($data['disable_subsequents']) {
          break;
        }
      }
    }

    return $static_labels_cache[$sku][$langcode][$type];
  }

  /**
   * Function to return labels files for a SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku Entity.
   * @param string $type
   *   Type of image required - plp or pdp.
   * @param bool $reset
   *   Flag to reset cache and generate array again from serialized string.
   *
   * @return array
   *   Array of media files.
   *
   * @todo Use self::getLabelsData() to get data and prepare images.
   */
  public function getLabels(SKU $sku_entity, $type = 'plp', $reset = FALSE) {
    $cache_key = 'sku_labels_' . $type;
    $cache = $reset
      ? NULL
      : $this->productCacheManager->get($sku_entity, $cache_key);

    if (isset($cache)) {
      return $cache;
    }

    $sku_labels = [];
    $labels_data = $this->getSkuLabel($sku_entity);

    $image_key = $type . '_image';
    $text_key = $type . '_image_text';
    $position_key = $type . '_position';

    foreach ($labels_data ?? [] as &$data) {
      $row = [];

      // Check if label is available for desired type.
      if (empty($data[$image_key])) {
        continue;
      }

      // Check if label is currently active.
      $from = strtotime($data['from'] ?? '');
      $to = strtotime($data['to'] ?? '');

      // First check if we have date filter.
      if ($from > 0 && $to > 0) {
        $now = $this->currentTime->getRequestTime();

        // Now, check if current date lies between from and to dates.
        if ($from > $now || $to < $now) {
          continue;
        }
      }

      $fid = $this->productLabelsCache->get($data[$image_key]);

      if (empty($fid)) {
        try {
          // Prepare the File object when we access it the first time.
          $fid = $this->downloadLabelsImage($sku_entity, $data, $image_key);
          $this->productLabelsCache->set($data[$image_key], $fid, CacheBackendInterface::CACHE_PERMANENT);
        }
        catch (\Exception $e) {
          $this->logger->error($e->getMessage());
          continue;
        }
      }
      else {
        $fid = $fid->data;
      }

      $image_file = $this->fileStorage->load($fid);
      $image = [
        '#theme' => 'image',
        '#uri' => $image_file->getFileUri(),
        '#title' => $data[$text_key],
        '#alt' => $data[$text_key],
      ];

      $row['image'] = $image;
      $row['position'] = $data[$position_key];
      $row['text'] = $data[$text_key];

      $sku_labels[] = $row;

      // Disable subsequent images if flag is true.
      if ($data['disable_subsequents']) {
        break;
      }
    }

    $this->productCacheManager->set($sku_entity, $cache_key, $sku_labels, $sku_entity->getCacheTags() ?? []);

    return $sku_labels;
  }

  /**
   * Function to get the product label for given SKU.
   *
   * First try to get the product label from SKU and then Check for
   * parent SKU if given SKU return empty file.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU entity object.
   * @param bool $parent
   *   True if current sku is parent SKU, default to FALSE.
   *
   * @return array
   *   Return array of labels data.
   */
  protected function getSkuLabel(SKU $sku_entity, $parent = FALSE) {
    if ($labels = $sku_entity->get('attr_labels')->getString()) {
      // phpcs:ignore
      $labels_data = unserialize($labels);
      if (!empty($labels_data) && is_array($labels_data)) {
        return $labels_data;
      }
      // Process only when current sku is not parent SKU.
      elseif (!$parent) {
        // Get parent sku of the sku.
        $parent_sku = $this->getParentSkuBySku($sku_entity);
        if (!empty($parent_sku)) {
          return $this->getSkuLabel($parent_sku, TRUE);
        }
      }
    }
    return [];
  }

  /**
   * Function to save image file into public dir.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU entity object.
   * @param array $data
   *   File data.
   * @param string $file_key
   *   File key.
   *
   * @return int
   *   File id.
   */
  protected function downloadLabelsImage(SKU $sku_entity, array $data, $file_key) {
    if (empty($data[$file_key])) {
      throw new \Exception('Image not available.');
    }

    // Preparing args for all info/error messages.
    $args = ['@file' => $data[$file_key], '@sku_id' => $sku_entity->id()];

    // Download the file contents.
    try {
      $options = [
        'timeout' => Settings::get('media_download_timeout', 5),
      ];

      $file_data = $this->httpClient->get($data[$file_key], $options)->getBody();
    }
    catch (RequestException $e) {
      watchdog_exception('alshaya_acm_product', $e);
    }

    // Check to ensure empty file is not saved in SKU.
    if (empty($file_data)) {
      throw new \Exception(new FormattableMarkup('Failed to download labels image file "@file" for SKU id @sku_id.', $args));
    }

    // Get the path part in the url, remove hostname.
    $path = parse_url($data[$file_key], PHP_URL_PATH);

    // Remove slashes from start and end.
    $path = trim($path, '/');

    // Get the file name.
    $file_name = basename($path);

    // Prepare the directory path.
    $directory = 'public://labels/' . str_replace('/' . $file_name, '', $path);

    // Prepare the directory.
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

    // Save the file as file entity.
    // @todo Check for a way to remove old files and file objects.
    // To be done here and in SKU.php both.
    /** @var \Drupal\file\Entity\File $file */
    if ($file = file_save_data($file_data, $directory . '/' . $file_name, FileSystemInterface::EXISTS_REPLACE)) {
      return $file->id();
    }
    else {
      throw new \Exception(new FormattableMarkup('Failed to save labels image file "@file" for SKU id @sku_id.', $args));
    }
  }

  /**
   * Helper function to fetch sku text from entity_id.
   *
   * @param string $entity_id
   *   Entity id for which sku text needs to be fetched.
   *
   * @return string
   *   SKU text corresponding to entity_id.
   */
  public function getSkuTextFromId($entity_id) {
    $sku_text = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['sku'])
      ->condition('asfd.id', $entity_id)
      ->range(0, 1)
      ->execute()->fetchField();

    return $sku_text;
  }

  /**
   * Helper function to fetch SKUs by langcode and type.
   *
   * @param string $langcode
   *   Language code.
   * @param string $type
   *   SKUs type (configurable, simple).
   *
   * @return array
   *   An array of SKUs.
   */
  public function getSkus($langcode, $type) {
    $query = $this->connection->select('acq_sku_field_data', 'asfd');
    $query->join('acq_sku_stock', 'stock', 'stock.sku = asfd.sku');

    $query->fields('asfd', ['sku', 'price', 'final_price']);
    $query->fields('stock', ['quantity', 'status']);

    $query->condition('type', $type, '=');
    $query->condition('langcode', $langcode, '=');

    return $query->execute()->fetchAllAssoc('sku', \PDO::FETCH_ASSOC);
  }

  /**
   * Get commerce category ids for the given skus.
   *
   * @param string $langcode
   *   Language code.
   * @param array $skus
   *   SKU list.
   *
   * @return array
   *   An array of SKU with commerce category ids.
   */
  public function getCategoriesOfSkus($langcode, array $skus) {
    // SQL 'IN' condition throws exception on empty array.
    if (empty($skus)) {
      return [];
    }

    $query = $this->connection->select('node__field_skus', 'nfs');
    $query->innerJoin('node__field_category_original', 'nfc', 'nfc.entity_id=nfs.entity_id AND nfc.langcode=nfs.langcode');
    $query->innerJoin('taxonomy_term__field_commerce_id', 'ttfcid', 'ttfcid.entity_id=nfc.field_category_original_target_id');
    $query->fields('ttfcid', ['field_commerce_id_value']);
    $query->fields('nfs', ['field_skus_value']);
    $query->condition('nfs.field_skus_value', $skus, 'IN');
    $query->condition('nfc.langcode', $langcode);

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Helper function to do a cheaper call to fetch SKUs for a promotion.
   *
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion for which we need to fetch SKUs.
   *
   * @return array
   *   List of skus related with a promotion.
   */
  public function getSkutextsForPromotion(NodeInterface $promotion) {
    $static = &drupal_static(__METHOD__, []);

    $cid = implode(':', ['promotion_sku', $promotion->id()]);

    if (!isset($static[$cid])) {
      // Fetch corresponding SKUs for promotion.
      $static[$cid] = $this->fetchSkuTextsForPromotion($promotion);
    }

    return $static[$cid];
  }

  /**
   * Prepares list of Promotion SKUs.
   *
   * @param \Drupal\node\NodeInterface $promotion
   *   Promotion for which we need to fetch SKUs.
   *
   * @return array
   *   Unique list of SKUs.
   */
  public function fetchSkuTextsForPromotion(NodeInterface $promotion) {
    // Get configurable and SKUs.
    $query = $this->connection->select('acq_sku_promotion', 'mapping');
    $query->join('acq_sku_field_data', 'asfd', 'asfd.sku = mapping.sku');
    $query->condition('mapping.rule_id', $promotion->get('field_acq_promotion_rule_id')->getString());
    $query->condition('asfd.type', ['simple', 'configurable'], 'IN');
    $query->fields('asfd', ['id', 'sku', 'type']);
    $query->distinct();
    $results = $query->execute()->fetchAll();

    $skus = [];
    $simple_skus = [];
    foreach ($results as $result) {
      switch ($result->type) {
        case 'simple':
          $simple_skus[$result->id] = $result->sku;
          break;

        case 'configurable':
          $skus[$result->id] = $result->sku;
          break;
      }
    }

    if ($simple_skus) {
      $skus = array_unique(array_merge($skus, $simple_skus));

      // Get all parent SKUs for simple ones.
      $parent_skus = $this->getParentSkus($simple_skus);
      $skus = array_unique(array_merge($skus, $parent_skus));
    }

    return $skus;
  }

  /**
   * Prepares unique list of child SKUs based on given configurable SKUs.
   *
   * @param array $skus
   *   List of configurable SKUs.
   *
   * @return array
   *   Unique List of child SKUs.
   */
  public function fetchChildSkuTexts(array $skus) {
    $query = $this->connection->select('acq_sku__field_configured_skus', 'asfcs');
    $query->join('acq_sku_field_data', 'asfd', 'asfd.id = asfcs.entity_id');
    $query->condition('asfd.sku', $skus, 'IN');
    $query->fields('asfcs', ['field_configured_skus_value']);
    $query->distinct();
    $childSkus = $query->execute()->fetchAll();

    if (!empty($childSkus)) {
      $childSkus = array_column($childSkus, 'field_configured_skus_value');
    }

    return $childSkus;
  }

  /**
   * Function to format composition field content.
   *
   * @param array $array
   *   Array of composition field data.
   * @param bool $list
   *   Boolean value to generate or not generate list.
   *
   * @return string
   *   UL / LI HTML list.
   */
  public function transformCompositionArrayToList(array $array, $list = TRUE) {
    $out = '';
    $materials = [];

    foreach ($array as $key => $elem) {
      if (!is_array($elem)) {
        $materials[] = "$key $elem%";
      }
      else {
        // Eliminate "materials" from the list.
        if ((strcasecmp($key, 'materials') === 0) ||
          (strcasecmp($key, 'undefined') === 0)) {
          $out .= $this->transformCompositionArrayToList($elem, FALSE);
        }
        else {
          $out .= "<li>";
          if ($key) {
            $out .= "$key: ";
          }

          $out .= $this->transformCompositionArrayToList($elem, FALSE) . "</li>";
        }
      }
    }

    if (!empty($materials)) {
      $out = implode('; ', $materials);
    }

    // Create the ul tags if there is an output.
    if ($list && $out) {
      // Add li tag for 1-D array.
      if (!empty($materials)) {
        $out = "<li>" . $out . "</li>";
      }
      $out = "<ul>" . $out . "</ul>";
    }

    return $out;
  }

  /**
   * Helper function to get parent skus of all simple ones in one go.
   *
   * @param array $simple_skus
   *   Array containing simple skus.
   *
   * @return array
   *   Array containing all parent skus.
   */
  public function getParentSkus(array $simple_skus) {
    $query = $this->connection->select('acq_sku_field_data', 'acq_sku');
    $query->addField('acq_sku', 'id');
    $query->addField('acq_sku', 'sku');
    $query->join('acq_sku__field_configured_skus', 'child_sku', 'acq_sku.id = child_sku.entity_id');
    $query->condition('child_sku.field_configured_skus_value', array_values($simple_skus), 'IN');
    return $query->execute()->fetchAllKeyed(0);
  }

  /**
   * Utility function to get parent node of the sku.
   *
   * @param mixed $sku
   *   SKU name or full sku object.
   * @param bool $check_parent
   *   Flag to check for parent sku or not (for configurable products).
   *
   * @return \Drupal\node\NodeInterface|null
   *   Loaded node object.
   */
  public function getDisplayNode($sku, $check_parent = TRUE) {
    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

    if (empty($sku_entity)) {
      $this->logger->warning('SKU entity not found for @sku. (@function)', [
        '@sku' => $sku,
        '@function' => 'SkuManager::getDisplayNode()',
      ]);
      return NULL;
    }

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku_entity->getPluginInstance();
    $node = $plugin->getDisplayNode($sku_entity, $check_parent);

    if (!($node instanceof NodeInterface)) {
      if ($check_parent) {
        $this->logger->warning('SKU entity available but no display node found for @sku with langcode: @langcode. SkuManager::getDisplayNode().', [
          '@langcode' => $sku_entity->language()->getId(),
          '@sku' => $sku_entity->getSku(),
        ]);
      }
      return NULL;
    }

    return $node;
  }

  /**
   * Utility function to get parent SKU for a configurable child sku.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param string $langcode
   *   Language code.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   Loaded SKU entity.
   */
  public function getParentSkuBySku($sku, $langcode = '') {
    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku, $langcode);

    // Additional check, can be removed post go UAT.
    if (empty($sku_entity)) {
      return NULL;
    }

    // Return NULL if we are already at parent level.
    if ($sku_entity->bundle() == 'configurable') {
      return NULL;
    }

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku_entity->getPluginInstance();

    if ($this->isSkuFreeGift($sku_entity)) {
      return $plugin->getParentSku($sku_entity, FALSE);
    }

    return $plugin->getParentSku($sku_entity);
  }

  /**
   * Utility function to get linked SKUs.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU full entity object.
   * @param string $type
   *   Type of Linked SKUs to return related/upsell.
   *
   * @return array
   *   Linked SKUs for requested type.
   */
  public function getLinkedSkus(SKU $sku, $type) {
    $linked_skus = $this->linkedSkus->getLinkedSkus($sku);

    $linked_skus_requested = [];

    if (isset($linked_skus[$type]) && !empty($linked_skus[$type])) {
      $linked_skus_requested = $linked_skus[$type];
    }

    try {
      if ($linked_skus_from_product = $sku->get($type)->getValue()) {
        $linked_skus_from_product = array_column($linked_skus_from_product, 'value');
        $linked_skus_requested = array_merge($linked_skus_requested, $linked_skus_from_product);
      }
    }
    catch (\Exception) {
      // Do nothing.
    }

    return $linked_skus_requested;
  }

  /**
   * Utility function to get linked SKUs for current and first child too.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU full entity object.
   * @param string $type
   *   Type of Linked SKUs to return related/upsell.
   *
   * @return array
   *   Linked SKUs for requested type.
   */
  public function getLinkedSkusWithFirstChild(SKU $sku, $type) {
    // First always get the parent if available.
    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku->getPluginInstance();
    $parent = $plugin->getParentSku($sku);
    $sku_entity = $parent instanceof SKU ? $parent : $sku;

    $linked_skus_requested = $this->getLinkedSkus($sku_entity, $type);

    $first_child = $this->getChildSkus($sku_entity, TRUE);

    if ($first_child) {
      $child_linked_skus_requested = $this->getLinkedSkus($first_child, $type);
      $linked_skus_requested = array_merge($linked_skus_requested, $child_linked_skus_requested);
    }

    return $linked_skus_requested;
  }

  /**
   * Helper function to filter skus by stock status.
   *
   * @param array $skus
   *   Array containing skus as string.
   *
   * @return array
   *   Filtered skus.
   */
  public function filterRelatedSkus(array $skus) {
    if (empty($skus)) {
      return [];
    }

    $related_items_size = $this->getConfig('alshaya_acm_product.settings')->get('related_items_size');
    $related = [];

    foreach ($skus as $sku) {
      try {
        $sku_entity = SKU::loadFromSku($sku);
        if (empty($sku_entity)) {
          continue;
        }

        $node = $this->getDisplayNode($sku_entity);
        if (empty($node)) {
          continue;
        }

        if ($this->isProductInStock($sku_entity)) {
          $related[$sku] = $node->id();
        }
      }
      catch (\Exception) {
        // Do nothing.
      }

      if (count($related) >= $related_items_size) {
        break;
      }
    }

    return $related;
  }

  /**
   * Helper function to fetch attributes for PDP.
   *
   * Use configurable SKU for configurable attributes & simple SKUs as source
   * for non-configurable attributes.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity for which the attribute data needs to be pulled.
   * @param string $attribute_machine_name
   *   Attribute field name.
   * @param string $search_direction
   *   Direction in which to look for fallback while fetching the attribute.
   * @param bool $multivalued
   *   Boolean value indicating if the field we looking for is multi-valued.
   *
   * @return array|string
   *   Attribute value.
   */
  public function fetchProductAttribute(SKU $sku, $attribute_machine_name, $search_direction, $multivalued = FALSE) {
    if (($search_direction == 'children') &&
      ($sku->getType() == 'configurable') &&
      ($child_sku = $this->getChildSkus($sku, TRUE))) {
      $sku = $child_sku;
    }
    elseif (($search_direction == 'parent') &&
      ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($sku))) {
      $sku = $parent_sku;
    }

    if ($sku instanceof SKU) {
      if (($multivalued) &&
        (!empty($first_index = $sku->get($attribute_machine_name)->first())) &&
        (!empty($attribute_value = $first_index->getString()))) {
        return $attribute_value;
      }
      elseif (!empty($attribute_value = $sku->get($attribute_machine_name)->getString())) {
        return $attribute_value;
      }
    }

    return '';
  }

  /**
   * Get possible combinations for a configurable SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   *
   * @return array
   *   Calculated combinations array.
   */
  public function getConfigurableCombinations(SKU $sku) {
    if ($sku->bundle() != 'configurable') {
      return [];
    }

    $static = &drupal_static(__METHOD__, []);

    $langcode = $sku->language()->getId();
    $sku_code = $sku->getSku();

    // Do not process the same thing again and again.
    if (isset($static[$langcode][$sku_code])) {
      return $static[$langcode][$sku_code];
    }

    /** @var \Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable $plugin */
    $plugin = $sku->getPluginInstance();
    $tree = $plugin->deriveProductTree($sku);

    $configurable_codes = array_keys($tree['configurables']);
    $all_combinations = $this->alshayaArrayUtils->getAllCombinations($configurable_codes);

    $combinations =& $tree['combinations'];

    // Don't store in cache and return empty array here if no valid
    // SKU / combination found.
    if (empty($combinations)) {
      // Below code is only for debugging issues around cache having empty data
      // even when there are children in stock.
      // @todo To be removed in: CORE-5271.
      // Done for: CORE-5200, CORE-5248.
      if ($this->isProductInStock($sku)) {
        // Log message here to allow debugging further.
        $this->logger->info($this->t('Found no combinations for SKU: @sku having language @langcode. Requested from @trace. Page: @page', [
          '@sku' => $sku_code,
          '@langcode' => $langcode,
          '@trace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), JSON_THROW_ON_ERROR),
          '@page' => $this->currentRequest->getRequestUri(),
        ]));
      }

      return [];
    }

    // Prepare combinations array grouped by attributes to check later which
    // combination is possible using isset().
    $combinations['by_attribute'] = [];

    foreach ($combinations['by_sku'] ?? [] as $combination) {
      foreach ($all_combinations as $possible_combination) {
        $combination_string = '';

        foreach ($possible_combination as $code) {
          if (!isset($combination[$code])) {
            $combination_string = '';
            break;
          }

          $combination_string .= $code . '|' . $combination[$code] . '||';
          $combinations['by_attribute'][$combination_string] = 1;
        }

        if (empty($combination_string)) {
          continue;
        }

        $combinations['by_attribute'][$combination_string] = 1;
      }
    }

    $static[$langcode][$sku_code] = $combinations;

    return $combinations;
  }

  /**
   * Get configurable attribute codes.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   *
   * @return array
   *   Configurable attribute codes for SKU.
   */
  public function getConfigurableAttributes(SKU $sku) {
    $combinations = $this->getConfigurableCombinations($sku);
    return $combinations ? array_keys($combinations['attribute_sku']) : [];
  }

  /**
   * Disable configurable options not available in the system.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   * @param array $configurables
   *   Configurables in the form.
   * @param array $tree
   *   Configurable tree from form state.
   * @param array $selected
   *   Selected values.
   */
  public function disableUnavailableOptions(SKU $sku, array &$configurables, array $tree, array &$selected = []) {
    $configurable_codes = array_keys($tree['configurables']);

    $combinations = $this->getConfigurableCombinations($sku);

    // Cleanup current selection.
    $selected = array_filter($selected);

    foreach ($selected as $code => $value) {
      // Check for selected values in current options.
      if (!isset($configurables[$code]['#options'][$value])) {
        unset($selected[$code]);
        continue;
      }
    }

    // Remove all options which are not available at all.
    foreach ($configurable_codes as $index => $code) {
      foreach ($configurables[$code]['#options'] as $key => $value) {
        if (empty($key) || isset($combinations['attribute_sku'][$code][$key])) {
          continue;
        }

        unset($configurables[$code]['#options'][$key]);
      }
    }

    $combination_key = '';
    foreach ($selected as $code => $value) {
      $index = array_search($code, $configurable_codes);
      if ($index !== FALSE) {
        unset($configurable_codes[$index]);
      }

      $combination_key .= $code . '|' . $value . '||';
      foreach ($configurable_codes as $configurable_code) {
        if (!isset($configurables[$configurable_code]) || empty($configurables[$configurable_code]['#options'])) {
          continue;
        }

        foreach ($configurables[$configurable_code]['#options'] as $key => $value) {
          $check_key1 = $combination_key . $configurable_code . '|' . $key . '||';
          $check_key2 = $configurable_code . '|' . $key . '||' . $combination_key;

          if (isset($combinations['by_attribute'][$check_key1])
            || isset($combinations['by_attribute'][$check_key2])) {
            continue;
          }

          if (isset($selected[$configurable_code]) && $selected[$configurable_code] == $key) {
            unset($selected[$configurable_code]);
            unset($configurables[$configurable_code]['#options_attributes'][$key]['selected']);
          }

          $configurables[$configurable_code]['#options_attributes'][$key]['disabled'] = 'disabled';
        }
      }
    }
  }

  /**
   * Helper function to get available options in form item.
   *
   * @param array $configurable
   *   Configurable attribute form item.
   *
   * @return array
   *   Available options.
   */
  public function getAvailableOptions(array $configurable) {
    $disabled_options = [];

    foreach ($configurable['#options_attributes'] ?? [] as $id => $options_attributes) {
      if (isset($options_attributes['disabled'])) {
        $disabled_options[$id] = $id;
      }
    }

    return array_diff(array_keys($configurable['#options']), $disabled_options);
  }

  /**
   * Wrapper function to update selected values in form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State object.
   * @param array $selected
   *   Selected values.
   */
  public function updatedFormSelected(FormStateInterface $form_state, array $selected) {
    $form_state->setValue('configurables', $selected);
    $user_input = $form_state->getUserInput();
    $user_input['configurables'] = $selected;
    $form_state->setUserInput($user_input);
  }

  /**
   * Get first child based on brand conditions if defined or from default.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param string $root_attribute_code
   *   Root attribute code.
   * @param array $selected
   *   Current selection.
   * @param array|null $root_attribute_form_item
   *   Form item containing options and disabled attributes.
   *
   * @return \Drupal\acq_sku\Entity\SKU
   *   First child SKU entity.
   */
  public function getFirstChildForSku(SKUInterface $sku, $root_attribute_code, array $selected = [], $root_attribute_form_item = []) {
    // Get the first child from user selected value if available.
    if (isset($selected[$root_attribute_code])) {
      $first_child = $this->getChildSkuFromAttribute($sku, $root_attribute_code, $selected[$root_attribute_code]);

      if ($first_child instanceof SKU) {
        return $first_child;
      }
    }

    // Select first child based on value provided in query params.
    $sku_id = (int) $this->getSelectedVariantId();

    // Give preference to sku id passed via query params.
    if ($sku_id && $sku_id != $sku->id()) {
      $first_child = $this->loadSkuById($sku_id);

      if ($first_child instanceof SKUInterface && $this->isProductInStock($first_child)) {
        return $first_child;
      }
    }

    // Default use-case: User landing on PDP from PLP/Search/directly.
    // Get the first child from sorted options of root attribute.
    if ($root_attribute_form_item) {
      foreach ($root_attribute_form_item['#options'] as $key => $value) {
        if (isset($root_attribute_form_item['#options_attributes'][$key]['disabled'])) {
          continue;
        }

        $root_attribute_first_value = $key;
        break;
      }

      if (isset($root_attribute_first_value)) {
        return $this->getChildSkuFromAttribute(
          $sku,
          $root_attribute_code,
          $root_attribute_first_value
        );
      }
    }

    // Fallback.
    return $this->getChildSkus($sku, TRUE);
  }

  /**
   * Helper function to get mode to use for displaying content on listing pages.
   *
   * @return string
   *   Mode to use for displaying content on listing pages.
   */
  public function getListingDisplayMode() {
    static $value = NULL;
    $value ??= $this->getConfig('alshaya_acm_product.display_settings')->get('listing_display_mode');
    return $value;
  }

  /**
   * Wrapper function to check if price mode is from to.
   *
   * @return bool
   *   TRUE if price mode is set to from to.
   */
  public function isPriceModeFromTo() {
    static $value = NULL;
    $value ??= $this->getConfig('alshaya_acm_product.display_settings')->get('price_display_mode') === SkuPriceHelper::PRICE_DISPLAY_MODE_FROM_TO;
    return $value;
  }

  /**
   * Helper function to check if display mode is aggregated.
   *
   * @return bool
   *   TRUE if mode is non-aggregated.
   */
  public function isListingDisplayModeAggregated() {
    return $this->getListingDisplayMode() === self::AGGREGATED_LISTING;
  }

  /**
   * Helper function to get attributes used for swatch on Listing.
   *
   * @return array
   *   Array containing attributes used for swatch on Listing.
   */
  public function getProductListingSwatchAttributes() {
    static $value = NULL;
    $value ??= $this->getConfig('alshaya_acm_product.display_settings')->get('swatches')['plp'] ?? ['actual_color_label_code'];
    return $value;
  }

  /**
   * Helper function to get attributes used for swatch on PDP.
   *
   * @return array
   *   Array containing attributes used for swatch on PDP.
   */
  public function getPdpSwatchAttributes() {
    static $value = NULL;
    $value ??= $this->getConfig('alshaya_acm_product.display_settings')->get('swatches')['pdp'] ?? ['color'];
    return $value;
  }

  /**
   * Helper function to get swatch attributes to exclude from PLP.
   *
   * @return array
   *   Array containing attributes to exclude.
   */
  public function getSwatchAttributesToExcludeOnPlp() {
    static $value = NULL;
    $value ??= $this->getConfig('alshaya_acm_product.display_settings')->get('exclude_swatches_on_plp') ?? [];
    return $value;
  }

  /**
   * Wrapper function to get value of swatch attribute for given SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   * @param array $configurable_attributes
   *   Configurable attributes used in the product.
   *
   * @return string|null
   *   Attribute value if found for the SKU.
   */
  public function getPdpSwatchValue(SKU $sku, array $configurable_attributes) {
    $swatch_attributes = array_intersect($this->getPdpSwatchAttributes(), $configurable_attributes);
    foreach ($swatch_attributes as $attribute_code) {
      $attributes = $sku->get('attributes')->getValue();
      $attributes = array_column($attributes, 'value', 'key');
      if (isset($attributes[$attribute_code]) && !empty($attributes[$attribute_code])) {
        return $attributes[$attribute_code];
      }
    }

    return NULL;
  }

  /**
   * Get Valid child skus as string for particular configurable product.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   *
   * @return array
   *   Child skus as string.
   */
  public function getValidChildSkusAsString(SKU $sku) {
    if ($sku->bundle() != 'configurable') {
      return [];
    }

    $static = &drupal_static(__METHOD__, []);

    if (!isset($static[$sku->getSku()])) {
      $static[$sku->getSku()] = [];

      $sku_variants = Configurable::getChildSkus($sku);
      $combinations = $this->getConfigurableCombinations($sku);

      // In some cases we modify combinations and add more children.
      // Here to get first valid we want only available ones from current
      // configurable sku.
      if (!empty($combinations['attribute_sku'])) {
        // Get the skus sorted by first attribute value.
        $combination_skus = array_reduce(reset($combinations['attribute_sku']), 'array_merge', []);
        $static[$sku->getSku()] = array_intersect($combination_skus, $sku_variants);
      }
    }

    return $static[$sku->getSku()] ?? [];
  }

  /**
   * Get first valid configurable child.
   *
   * For a configurable product, we may have many children as disabled or OOS.
   * We don't show them as selected on page load. Here we find the first one
   * which is enabled and in stock.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Configurable SKU entity.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   Valid child SKU or parent itself.
   */
  public function getFirstValidConfigurableChild(SKU $sku) {
    if ($sku->bundle() != 'configurable') {
      return [];
    }

    $static = &drupal_static(__METHOD__, []);

    $langcode = $sku->language()->getId();
    $id = $sku->id();

    if (!isset($static[$langcode][$id])) {
      $static[$langcode][$id] = NULL;

      $sku_variants = $this->getValidChildSkusAsString($sku);

      if (!empty($sku_variants)) {
        $variant_sku_code = reset($sku_variants);
        $static[$langcode][$id] = SKU::loadFromSku($variant_sku_code);
      }
    }

    return $static[$langcode][$id];
  }

  /**
   * Get first valid configurable child.
   *
   * For a configurable product, we may have many children as disabled or OOS.
   * We don't show them as selected on page load. Here we find the first one
   * which is enabled and in stock.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Configurable SKU entity.
   *
   * @return \Drupal\acq_sku\Entity\SKU
   *   Valid child SKU or parent itself.
   */
  public function getFirstAvailableConfigurableChild(SKU $sku) {
    if ($sku->bundle() != 'configurable') {
      return $sku;
    }

    $children = Configurable::getChildren($sku);
    return reset($children);
  }

  /**
   * Utility function to return configurable values for a SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   *
   * @return array
   *   Array of configurable field values.
   */
  public function getConfigurableValues(SKUInterface $sku): array {
    $configurableFieldValues = [];

    if ($sku->bundle() == 'configurable') {
      $configurable_attributes = $this->getConfigurableAttributes($sku);
    }
    else {
      $parent = $this->getParentSkuBySku($sku);
      if ($parent instanceof SKU) {
        $configurable_attributes = $this->getConfigurableAttributes($parent);
      }
    }

    if (empty($configurable_attributes)) {
      return $configurableFieldValues;
    }

    $fields = $this->skuFieldsManager->getFieldAdditions();
    $configurableFieldReplacements = array_filter($fields, fn($field) => !empty($field['display_configurable_for']));

    // For some fields we display from different attribute.
    // For instance for article_castor_id we display from color_label.
    foreach ($configurableFieldReplacements as $code => $field) {
      $index = array_search($field['display_configurable_for'], $configurable_attributes);
      if ($index !== 'false') {
        $configurable_attributes[$index] = $code;
      }
    }

    $remove_not_required_option = $this->isNotRequiredOptionsToBeRemoved();

    foreach ($configurable_attributes as $code) {
      $fieldKey = 'attr_' . $code;
      if ($sku->hasField($fieldKey)) {
        $value = $sku->get($fieldKey)->getString();
        $context = $this->requestContextManager->getContext();
        if ($context != 'app' && $remove_not_required_option && $this->isAttributeOptionToExclude($value)) {
          continue;
        }

        // Get raw attributes values for configurable options.
        // Check if this attribute code is replaced earlier and fetch the actual
        // attribute code from `display_configurable_for` field and get the raw
        // option data using that. For example `color_label` attribute code is
        // used for `article_castor_id` attribute code and we will found that
        // attribute only in configurable_attributes array of product_tree.
        $code = !empty($configurableFieldReplacements)
          && isset($configurableFieldReplacements[$code]) ? $configurableFieldReplacements[$code]['display_configurable_for'] : $code;
        $raw_options = $this->getConfigurableRawAttributesData($sku, $code);

        $configurableFieldValues[$fieldKey] = [
          'attribute_id' => $fieldKey,
          'label' => $this->getLabelFromParentSku($sku, $code) ?? (string) $sku->get($fieldKey)
            ->getFieldDefinition()
            ->getLabel(),
          'value' => $sku->get($fieldKey)->getString(),
          'option_id' => $raw_options['option_id'] ?? '' ,
          'option_value' => $raw_options['option_value'] ?? '',
        ];
      }
      else {
        // If attribute code is not sku field eg: subset_name, then it's a
        // configurable form setting hence get raw attributes data for wishlist
        // product options.
        $raw_options = $this->getConfigurableRawAttributesData($sku, $code);
        // Skip if raw options does not have id or value.
        if (empty($raw_options['option_id']) || empty($raw_options['option_value'])) {
          continue;
        }
        // Keep label and value null as these attributes eg: subset_name
        // should not be shown on cart product attributes and used only for
        // wish-list selected variant.
        $configurableFieldValues[$code] = [
          'attribute_id' => $code,
          'label' => NULL,
          'value' => NULL,
          'option_id' => $raw_options['option_id'] ?? '' ,
          'option_value' => $raw_options['option_value'] ?? '',
        ];
      }
    }

    return $configurableFieldValues;
  }

  /**
   * Utility function to return label from parent sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param string $attr_code
   *   Attribute code.
   *
   * @return string
   *   Label for configurable fields.
   */
  public function getLabelFromParentSku(SKUInterface $sku, $attr_code) {
    $parent_sku = $this->getParentSkuBySku($sku);
    if ($parent_sku instanceof SKUInterface) {
      // phpcs:ignore
      $configurables = unserialize($parent_sku->get('field_configurable_attributes')->getString());
      foreach ($configurables as $field) {
        if ($attr_code == $field['code']) {
          return $field['label'];
        }
      }
    }
    return NULL;
  }

  /**
   * Check if we need to process and hide not required options.
   *
   * @return bool
   *   TRUE if we need to process and hide not required options.
   */
  public function isNotRequiredOptionsToBeRemoved() {
    static $value = NULL;
    $value ??= (bool) $this->getConfig('alshaya_acm_product.display_settings')->get('hide_not_required_option');
    return $value;
  }

  /**
   * Show images from child only after all options are selected or not.
   *
   * @return bool
   *   TRUE if we need to show from child only after all options are selected.
   */
  public function showImagesFromChildrenAfterAllOptionsSelected(): bool {
    static $value = NULL;
    $value ??= $this->getConfig('alshaya_acm_product.display_settings')->get('show_child_images_after_selecting') == 'all';
    return $value;
  }

  /**
   * Wrapper function to check if value matches options value to exclude.
   *
   * @param string $value
   *   Attribute option value to check.
   *
   * @return bool
   *   TRUE if value matches options value to exclude.
   */
  public function isAttributeOptionToExclude($value) {
    return in_array($value, $this->attributeOptionToExclude());
  }

  /**
   * Wrapper function to get options value to exclude.
   *
   * @return array
   *   Array of values to exclude.
   */
  public function attributeOptionToExclude() {
    $static = &drupal_static(__METHOD__, []);
    if (!isset($static['excluded_attribute_options'])) {
      $static['excluded_attribute_options'] = $this->getConfig('alshaya_acm_product.settings')->get('excluded_attribute_options');
    }
    return $static['excluded_attribute_options'];
  }

  /**
   * Process a configurable attribute to remove not required items.
   *
   * @param array $configurable
   *   Configurable attribute form item.
   */
  public function processAttribute(array &$configurable) {
    if (!$this->isNotRequiredOptionsToBeRemoved()) {
      return;
    }
    $availableOptions = [];
    $notRequiredValue = NULL;
    foreach ($configurable['#options'] as $id => $value) {

      // @todo CORE-13213, temporarily disabling this.
      if ($this->isAttributeOptionToExclude($value) && 1 == 2) {
        $configurable['#options_attributes'][$id]['class'][] = 'hidden';
        $configurable['#options_attributes'][$id]['class'][] = 'visually-hidden';
        $notRequiredValue = $id;
      }
      elseif (empty($configurable['#options_attributes'][$id]['disabled'])) {
        $availableOptions[$id] = $value;
      }
    }
    if ($notRequiredValue && empty($availableOptions)) {
      $configurable['#value'] = $notRequiredValue;
      $configurable['#access'] = FALSE;
    }
  }

  /**
   * Helper function to fetch image slide position on pdp for the sku or node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity for which image slider position needs to be fetched.
   *
   * @return string
   *   Image slider position type for the sku.
   */
  public function getImageSliderPosition(EntityInterface $entity) {
    $default_pdp_image_slider_position = $this->getConfig('alshaya_acm_product.settings')
      ->get('image_slider_position_pdp');
    if ($entity instanceof SKUInterface) {
      $entity = $this->getDisplayNode($entity);
    }

    if (($entity instanceof NodeInterface) && $entity->bundle() === 'acq_product' && ($term_list = $entity->get('field_category')->getValue())) {
      $inner_term = $this->productCategoryHelper->termTreeGroup($term_list);
      if ($inner_term) {
        $term = $this->termStorage->load($inner_term);
        if ($term instanceof TermInterface) {
          if ($pdp_image_slider_position = $this->getImagePositionFromTerm($term)) {
            return $pdp_image_slider_position;
          }
        }
        $taxonomy_parents = $this->termStorage->loadAllParents($inner_term);
        foreach ($taxonomy_parents as $taxonomy_parent) {
          if ($pdp_image_slider_position = $this->getImagePositionFromTerm($taxonomy_parent)) {
            return $pdp_image_slider_position;
          }
        }
      }
      $pdp_image_slider_position = (!empty($pdp_image_slider_position)) ? $pdp_image_slider_position : $default_pdp_image_slider_position;
      return $pdp_image_slider_position;
    }
    else {
      // In the rare case that the products are not assigned any category, we
      // want to return the default setting for position so the PDP works.
      return $default_pdp_image_slider_position;
    }
  }

  /**
   * Helper function to fetch image slide position for term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Taxonomy term for which image slider position needs to be fetched.
   *
   * @return string|null
   *   Image slider position type for the term.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getImagePositionFromTerm(TermInterface $term) {
    if ($term->get('field_pdp_image_slider_position')->first()) {
      return $term->get('field_pdp_image_slider_position')
        ->getString();
    }
    return NULL;
  }

  /**
   * Helper function to check if sku is a free gift.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity to check.
   *
   * @return bool
   *   TRUE if free gift, else FALSE.
   *
   * @throws \InvalidArgumentException
   */
  public function isSkuFreeGift(SKU $sku) {
    $price = (float) $sku->get('price')->getString();
    $final_price = (float) $sku->get('final_price')->getString();
    return ($price == self::FREE_GIFT_PRICE) || ($final_price == self::FREE_GIFT_PRICE);
  }

  /**
   * Check if SKU has data available for all configurable attributes.
   *
   * If SKU has configurable attributes (for sku form) but no data is
   * available for any one configurable attribute, it means we cant show that
   * sku with 'add to cart' form and we show OOS for that SKU. Even attribute
   * combination value is disabled but there should be some value for that
   * attribute in the system.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity to check.
   *
   * @return bool
   *   True if SKU has data for all configurable attributes.
   */
  public function skuAttributeCombinationsValid(SKUInterface $sku): bool {
    $static = &drupal_static('skuAttributeCombinationsValid', []);

    if (isset($static[$sku->id()])) {
      return $static[$sku->id()];
    }

    // This is only for configurable SKU. For simple sku, we don't have/show
    // any configurable on sku form and thus we always return true.
    if ($sku->bundle() == 'configurable') {
      $combinations = $this->getConfigurableCombinations($sku);

      if (empty($combinations) || empty($combinations['attribute_sku'])) {
        $static[$sku->id()] = FALSE;
        return FALSE;
      }
    }

    $static[$sku->id()] = TRUE;
    return TRUE;
  }

  /**
   * Check if product (SKU) is in stock or not.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return bool
   *   TRUE if product is in stock.
   */
  public function isProductInStock(SKUInterface $sku): bool {
    $cache = $this->productCacheManager->get($sku, 'in_stock');

    if (isset($cache)) {
      return $cache;
    }

    // For all web requests we don't want to show the products
    // that are not processed yet except for free gifts.
    if (PHP_SAPI != 'cli'
      && !($this->isSkuFreeGift($sku))
      && !($this->productProcessedManager->isProductProcessed($sku->getSku()))) {
      return FALSE;
    }

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku->getPluginInstance();
    $in_stock = $plugin->isProductInStock($sku);

    if ($in_stock && $sku->bundle() == 'configurable') {
      $children = $this->getInStockNonFreeGiftChildren($sku);

      // Check if there are in-stock children available.
      // (Excluding free gifts and OOS).
      $in_stock = (count($children) > 0);
    }

    $this->productCacheManager->set($sku, 'in_stock', $in_stock, [StockResource::CACHE_PREFIX . $sku->id()]);

    return $in_stock;
  }

  /**
   * Get in-stock non free gift variants for particular sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Array of variant skus.
   */
  public function getInStockNonFreeGiftChildren(SKUInterface $sku) {
    $static = &drupal_static(__METHOD__, []);

    if (!isset($static[$sku->id()])) {
      $variants = Configurable::getChildSkus($sku);

      // Avoid fatal error because fo faulty data.
      if (empty($variants)) {
        return [];
      }

      $query = $this->connection->select('acq_sku_field_data', 'asfd');

      // Check only for children of current parent.
      $query->condition('asfd.sku', $variants, 'IN');

      // Restrict to one/default language records.
      $query->condition('asfd.default_langcode', 1);

      // Non-free gift.
      $query->condition('asfd.price', static::FREE_GIFT_PRICE, '!=');
      $query->condition('asfd.final_price', static::FREE_GIFT_PRICE, '!=');

      // In stock.
      $query->innerJoin('acq_sku_stock', 'stock', 'asfd.sku = stock.sku');
      $query->condition('stock.quantity', 0, '>');
      $query->condition('stock.status', 0, '>');

      // Adding this to reduce result to check from stock table.
      // This is a work around to improve performance of the query.
      $query->condition('stock.sku', $variants, 'IN');

      // Select the sku.
      $query->fields('asfd', ['sku']);

      $static[$sku->id()] = $query->execute()->fetchAllKeyed(0, 0);
    }

    return $static[$sku->id()];
  }

  /**
   * Get stock quantity for a product.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return int
   *   Stock quantity.
   */
  public function getStockQuantity(SKUInterface $sku): int {
    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku->getPluginInstance();
    return (int) $plugin->getStock($sku);
  }

  /**
   * Check if product (node) is in stock or not.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if product is in stock.
   */
  public function isProductNodeInStock(NodeInterface $node): bool {
    $sku_string = $this->getSkuForNode($node);

    $sku = SKU::loadFromSku($sku_string);
    if ($sku instanceof SKUInterface) {
      return $this->isProductInStock($sku);
    }

    return FALSE;
  }

  /**
   * Get selected variant for a product on page load.
   *
   * To be used mainly to get child if only one child is available.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return \Drupal\acq_commerce\SKUInterface|null
   *   NULL if not able to find a single variant to use.
   */
  public function getSelectedVariant(SKUInterface $sku): ?SKUInterface {
    // We need to find variant only for configurable ones.
    if ($sku->bundle() != 'configurable') {
      return $sku;
    }

    // No variant selection if product is OOS.
    if (!$this->isProductInStock($sku)) {
      return NULL;
    }

    $static = &drupal_static('getSelectedVariant', []);

    if (isset($static[$sku->id()])) {
      return $static[$sku->id()];
    }

    $combinations = $this->getConfigurableCombinations($sku);

    // If there is only one child, we select that by default.
    if ((is_countable($combinations['by_sku']) ? count($combinations['by_sku']) : 0) === 1) {
      $child_skus = array_keys($combinations['by_sku']);
      $child_sku = reset($child_skus);
      if ($child = SKU::loadFromSku($child_sku, $sku->language()->getId())) {
        $this->setSelectedVariantId($child->id());
        $static[$sku->id()] = $child;
        return $child;
      }
    }

    $select_from_query = TRUE;

    // Check if we need the select the variant only after all
    // options are selected.
    if ($this->showImagesFromChildrenAfterAllOptionsSelected()) {
      // If there is only one attribute, we will have selection by default.
      // If there are more then one attributes, we will select by default only
      // if there is one value in that specific attribute.
      // Here we say not to select variant from query ?selected=xxx if there
      // is any attribute (except the first one) which has more then one value.
      if ((is_countable($combinations['attribute_sku']) ? count($combinations['attribute_sku']) : 0) > 1) {
        foreach ($combinations['attribute_sku'] as $values) {
          // Get the SKUs attached with first option of attribute.
          $first_attribute_index = key($values);
          // If only one sku is attached with the first option of the first
          // attribute, it means only one sku will be available for that
          // combination and thus that will also be selected as well.
          if ((is_countable($values[$first_attribute_index]) ? count($values[$first_attribute_index]) : 0) == 1) {
            break;
          }

          // If more than one options for the attribute available or more than
          // one skus attached with the first option of first attribute, means
          // full selection of attributes is not made.
          if ((is_countable($values) ? count($values) : 0) > 1 || (is_countable($values[$first_attribute_index]) ? count($values[$first_attribute_index]) : 0) > 1) {
            $select_from_query = FALSE;
            break;
          }
        }
      }
    }

    // If there is only one attribute option or config says select one
    // child if only one attribute is selected, process further.
    if ($select_from_query) {
      // Select first child based on value provided in query params.
      $sku_id = (int) $this->getSelectedVariantId();

      if ($sku_id && $sku_id != $sku->id()) {
        $selected_sku = $this->loadSkuById($sku_id);

        if ($selected_sku instanceof SKUInterface && $this->isProductInStock($selected_sku)) {
          $static[$sku->id()] = $selected_sku;
          return $selected_sku;
        }
        else {
          // Set it to NULL to indicate code below that we didn't change.
          $this->setSelectedVariantId(NULL);
        }
      }

      $selected_sku = $this->getFirstValidConfigurableChild($sku);
      if ($selected_sku instanceof SKUInterface) {
        $this->setSelectedVariantId($selected_sku->id());
        return $selected_sku;
      }
    }

    return NULL;
  }

  /**
   * Helper function to fetch pdp layout to be used for the sku or node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity for which layout needs to be fetched.
   * @param string $context
   *   Context for which layout needs to be fetched.
   *
   * @return string
   *   PDP layout to be used.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getPdpLayout(EntityInterface $entity, $context = 'pdp') {
    if ($entity instanceof SKUInterface) {
      $entity = $this->getDisplayNode($entity);
    }

    $static = &drupal_static(__FUNCTION__, []);

    // Load default from config.
    if (!isset($static['default'])) {
      $static['default'] = $this->getConfig('alshaya_acm_product.settings')->get('pdp_layout');
    }

    // If we don't have product node, let's just return default.
    if (!($entity instanceof NodeInterface)
      || !in_array($entity->bundle(), ['acq_product', 'rcs_product'])) {
      return $this->getContextFromLayoutKey($context, $static['default']);
    }

    // Return from static cache if we already have it processed once.
    if (isset($static[$entity->id()])) {
      return $this->getContextFromLayoutKey($context, $static[$entity->id()]);
    }

    // Set default in static, we will override below if we have something
    // available from terms.
    $static[$entity->id()] = $static['default'];

    // The layout has been overriden at node level.
    if ($entity instanceof NodeInterface && !empty($entity->get('field_select_pdp_layout')->value)) {
      $static[$entity->id()] = $entity->get('field_select_pdp_layout')->value;
    }

    // The layout has been overriden at category level.
    elseif (($entity->hasField('field_category')) && ($term_list = $entity->get('field_category')->getValue())) {
      $terms = array_column($term_list, 'target_id');
      $applied_layout = $this->getPdpLayoutFromCategories($terms);
      if ($applied_layout != NULL) {
        $static[$entity->id()] = $applied_layout;
      }
    }

    return $this->getContextFromLayoutKey($context, $static[$entity->id()]);
  }

  /**
   * Gets pdp layout from categories.
   *
   * @param array $terms
   *   Terms array tagged to product.
   *
   * @return mixed
   *   PDP layout or null.
   */
  private function getPdpLayoutFromCategories(array $terms) {
    $applied_layout = NULL;

    // Get the layout config for these terms.
    $terms_layouts = $this->getFieldPdpLayout($terms);
    $terms_to_explore = [];
    foreach ($terms_layouts as $term_layout) {
      if (!empty($term_layout->field_pdp_layout_value)) {
        if ($term_layout->field_pdp_layout_value == self::PDP_LAYOUT_INHERIT_KEY) {
          $terms_to_explore[] = $term_layout->entity_id;
        }
        else {
          $applied_layout = $term_layout->field_pdp_layout_value;
          break;
        }
      }
    }

    if (empty($applied_layout) && !empty($terms_to_explore)) {
      $parents = $this->getParentsIds($terms_to_explore);
      if (!empty($parents)) {
        $applied_layout = $this->getPdpLayoutFromCategories($parents);
      }
    }

    return $applied_layout;
  }

  /**
   * Get term's field pdp layout values and their parents.
   *
   * @param array $terms
   *   Term ids array.
   *
   * @return mixed
   *   Query result.
   */
  private function getFieldPdpLayout(array $terms) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->connection->select('taxonomy_term__field_pdp_layout', 'ttfpl');
    $query->fields('ttfpl', ['entity_id', 'field_pdp_layout_value']);
    $query->condition('ttfpl.bundle', 'acq_product_category');
    $query->condition('ttfpl.entity_id', $terms, 'IN');
    $query->condition('ttfpl.langcode', $langcode);

    return $query->execute()->fetchAll();
  }

  /**
   * Get Parent terms from query result.
   *
   * @param array $terms
   *   Terms array from query result.
   *
   * @return array
   *   Parent term ids.
   */
  private function getParentsIds(array $terms) {
    $parents = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->connection->select('taxonomy_term__parent', 'ttp');
    $query->fields('ttp', ['parent_target_id']);
    $query->condition('ttp.entity_id', $terms, 'IN');
    $query->condition('ttp.parent_target_id', 0, '!=');
    $query->condition('ttp.langcode', $langcode);
    $parents = $query->execute()->fetchAllKeyed(0, 0);

    return array_values($parents);
  }

  /**
   * Helper function to fetch pdp layout to be used for the sku or node.
   *
   * @param string $context
   *   Context for which layout needs to be fetched.
   * @param string $pdp_layout
   *   Context for which layout needs to be fetched.
   *
   * @return string
   *   PDP layout context to be used.
   */
  public function getContextFromLayoutKey($context, $pdp_layout) {
    $context_key = $context;
    $this->moduleHandler->alter('alshaya_context_key_from_layout', $context_key, $pdp_layout);
    return $context_key;
  }

  /**
   * Helper function to fetch pdp layout for a particular Term ID.
   *
   * @param int $tid
   *   Term ID for which layout needs to be fetched.
   *
   * @return string
   *   PDP layout to be used.
   */
  public function getPdpLayoutFromTermId($tid) {
    $default_pdp_layout = $this->getConfig('alshaya_acm_product.settings')->get('pdp_layout');

    $term = $this->termStorage->load($tid);
    if ($term instanceof TermInterface && $term->bundle() == ProductCategoryTree::VOCABULARY_ID) {
      $context = 'pdp';
      if ($term->get('field_pdp_layout')->first()) {
        $pdp_layout = $term->get('field_pdp_layout')->getString();
        if ($pdp_layout == self::PDP_LAYOUT_INHERIT_KEY) {
          foreach ($this->termStorage->loadAllParents($tid) as $taxonomy_parent) {
            $pdp_layout = $taxonomy_parent->get('field_pdp_layout')->getString() ?? NULL;
            if ($pdp_layout != NULL && $pdp_layout != self::PDP_LAYOUT_INHERIT_KEY) {
              return $this->getContextFromLayoutKey($context, $pdp_layout);
            }
          }
        }
        else {
          return $this->getContextFromLayoutKey($context, $pdp_layout);
        }
      }

      return $this->getContextFromLayoutKey($context, $default_pdp_layout);
    }

    return $default_pdp_layout;
  }

  /**
   * Add/update/translate node for specific color.
   *
   * @param \Drupal\node\NodeInterface $original
   *   Original node for configurable parent.
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   * @param string $color
   *   Color for which we want to create nodes.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Color Node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processColorNode(NodeInterface $original, SKU $sku, $color): ?NodeInterface {
    $data = [
      'field_skus' => $sku->getSku(),
      'field_product_color' => $color,
    ];

    $langcode = $original->language()->getId();
    $nodes = $this->nodeStorage->loadByProperties($data);

    /** @var \Drupal\node\NodeInterface $node */
    $node = NULL;

    if (empty($nodes)) {
      $data['type'] = 'acq_product';
      $data['langcode'] = $langcode;
      $data['path'] = [
        'alias' => '/color-node-' . $sku->getSku() . '-' . $color,
        'pathauto' => PathautoState::SKIP,
      ];

      $node = $this->nodeStorage->create($data);

      $this->logger->info('Creating color node for color: @color, parent sku: @sku, langcode: @langcode', [
        '@color' => $color,
        '@sku' => $sku->getSku(),
        '@langcode' => $langcode,
      ]);
    }
    else {
      $node = reset($nodes);

      if ($node->language()->getId() == $langcode) {
        // Do nothing.
      }
      // If node has translation, we return the translation.
      elseif ($node->hasTranslation($langcode)) {
        $node = $node->getTranslation($langcode);
      }
      // If translation not available.
      else {
        $node = $node->addTranslation($langcode);
        $this->logger->info('Adding translation for color: @color, parent sku: @sku, langcode: @langcode', [
          '@color' => $color,
          '@sku' => $sku->getSku(),
          '@langcode' => $langcode,
        ]);
      }
    }

    $node->setCreatedTime($original->getCreatedTime());
    $node->get('field_skus')->setValue($sku->getSku());
    $node->get('field_product_color')->setValue($color);
    $node->get('title')->setValue($original->label());
    $node->get('field_category')->setValue($original->get('field_category')->getValue());
    $node->get('body')->setValue($original->get('body')->getValue());
    $node->save();

    // Since we already have main node and this is additional node that we
    // just create for indexing things properly, we actually do not want this
    // to be indexed in xml sitemap or image sitemap. We mark this node as
    // non-indexable.
    // Code below is copied from simple_sitemap_entity_form_submit().
    $settings = [
      'index' => 0,
      'priority' => '0.5',
      'changefreq' => 'never',
      'include_images' => '0',
    ];

    $this->generator->setEntityInstanceSettings('node', $node->id(), $settings);

    return $node;
  }

  /**
   * Wrapper function to get SKU for particular product node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   * @param bool $no_color_node
   *   Flag to specify we do not want to get sku for color nodes.
   *
   * @return string
   *   SKU string if found.
   */
  public function getSkuForNode(NodeInterface $node, $no_color_node = FALSE) {
    $sku_string = $node->get('field_skus')->getString();

    $product_color = ($this->isListingModeNonAggregated()) ? $node->get('field_product_color')->getString() : '';

    if ($no_color_node && $product_color) {
      return '';
    }

    return $sku_string;
  }

  /**
   * Wrapper function to process all color nodes from it.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Parent product node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processColorNodesForConfigurable(NodeInterface $node) {
    if ($this->isListingDisplayModeAggregated()) {
      return;
    }

    $sku_string = $this->getSkuForNode($node, TRUE);

    if (empty($sku_string)) {
      return;
    }

    $langcode = $node->language()->getId();
    $sku = SKU::loadFromSku($sku_string, $langcode);

    if (!($sku instanceof SKUInterface)) {
      return;
    }

    // Get existing color nodes.
    // We will delete ones which are no longer available.
    $nids = array_flip($this->getColorNodeIds($sku->getSku()));

    $colors = [];
    $configurable_attributes = $this->getConfigurableAttributes($sku);
    foreach ($this->getAvailableChildren($sku) ?? [] as $child) {
      $child_color = $this->getPdpSwatchValue($child, $configurable_attributes);

      if (!empty($child_color) && !isset($colors[$child_color])) {
        // Create the node if not available.
        $colorNode = $this->processColorNode(
          $node,
          $sku,
          $child_color
        );

        $colors[$child_color] = $colorNode->id();
        unset($nids[$colorNode->id()]);
      }
    }

    // Delete all the nodes for which color nodes were not updated now.
    if ($nids) {
      try {
        $nids = array_flip($nids);
        $nodes = $this->nodeStorage->loadMultiple($nids);
        $this->nodeStorage->delete($nodes);
        $this->logger->info('Deleted color nodes as no variants available now for them. Color node ids: @ids, Parent Node id: @id', [
          '@ids' => implode(',', $nids),
          '@id' => $node->id(),
        ]);
      }
      catch (\Exception $e) {
        $this->logger->error('Error while deleting color nodes: @nids of parent node: @pid Message: @message in method: @method', [
          '@nids' => implode(',', $nids),
          '@pid' => $node->id(),
          '@message' => $e->getMessage(),
          '@method' => 'SkuManager::processColorNodesForConfigurable',
        ]);
      }
    }
  }

  /**
   * Get nids for all color nodes of particular sku.
   *
   * @param string $sku
   *   SKU as string.
   *
   * @return array
   *   Array of nids.
   */
  public function getColorNodeIds(string $sku) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_product');
    $query->condition('field_skus', $sku);
    $query->exists('field_product_color');
    return $query->execute();
  }

  /**
   * Helper function to process index item.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param \Drupal\search_api\Item\ItemInterface $item
   *   Index item.
   *
   * @throws \Exception
   */
  public function processIndexItem(NodeInterface $node, ItemInterface $item) {
    // Disable alshaya_color_split hook calls.
    SkuManager::$colorSplitMergeChildren = FALSE;
    $langcode = $node->language()->getId();

    $sku_string = $this->getSkuForNode($node);
    $sku = SKU::loadFromSku($sku_string, $langcode);

    if (!($sku instanceof SKUInterface)) {
      throw new \Exception('Not able to load sku from node.');
    }
    elseif ($sku->language()->getId() != $langcode) {
      throw new \Exception('SKU not available for language of Node');
    }

    // Set nid to original node's id.
    $original = $this->getDisplayNode($sku);

    if (!($original instanceof NodeInterface)) {
      throw new \Exception('Unable to load original node.');
    }

    $nid_field = $item->getField('original_nid');
    if ($nid_field) {
      $nid_field->setValues([$original->id()]);
    }
    $product_color = '';
    if ($this->isListingModeNonAggregated()) {
      $product_color = $node->get('field_product_color')->getString();
    }

    $prices = $this->getMinPrices($sku, $product_color);
    $item->getField('price')->setValues([$prices['price']]);
    $item->getField('final_price')->setValues([$prices['final_price']]);

    // Use max of selling prices for price in configurable products.
    if (!empty($prices['children'])) {
      $selling_prices = array_filter(array_column($prices['children'], 'selling_price'));
      $item->getField('price')->setValues([max($selling_prices)]);

      $selling_prices = array_unique([
        min($selling_prices),
        max($selling_prices),
      ]);
      $item->getField('attr_selling_price')->setValues($selling_prices);

      if ($this->isPriceModeFromTo()) {
        $item->getField('final_price')->setValues([min($selling_prices)]);
      }
    }

    // Process and store promotion fields.
    // We use placeholders for creating the fields in search_api indexes
    // (solr/db). For instance to store promotion_nid we configure it with "nid"
    // of product itself in search_api.index.product and process + store actual
    // values here. We do this for original_nid, promotion_nid and
    // field_acq_promotion_label as of today.
    $fields = $item->getFields();
    $promotions = $this->getPromotionsForSearchViewFromSkuId($sku);
    if (isset($fields['promotion_nid'])) {
      $item->getField('promotion_nid')->setValues(array_keys($promotions));
    }
    if (isset($fields['field_acq_promotion_label'])) {
      $item->getField('field_acq_promotion_label')->setValues(array_column($promotions, 'text'));
    }

    if ($sku->bundle() === 'configurable') {
      $this->processIndexItemConfigurable($sku, $item, $product_color);
    }
    elseif ($sku->bundle() == 'simple') {
      if ($this->isSkuFreeGift($sku)) {
        throw new \Exception('SKU is free gift sku');
      }
    }

    $this->updateStockForIndex($sku, $item);
  }

  /**
   * Wrapper function to update stock data for index item.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param \Drupal\search_api\Item\ItemInterface $item
   *   Index item.
   */
  private function updateStockForIndex(SKUInterface $sku, ItemInterface $item) {
    // We will use node.sticky to map stock status in index.
    // For stock index, we use only in stock (1) or out of stock (0).
    // We will use 1 for not-buyable products too.
    $in_stock = $this->getStockStatusForIndex($sku);

    if ($in_stock === 0) {
      // If product is not in stock, remove all attributes data.
      // Get indexed fields.
      $fields = $item->getFields();

      // Iterate over each indexed field.
      foreach ($fields as $field_key => $field_val) {
        // Only unset/remove of attribute fields or this will remove the
        // SKU from the indexing on default listing (without any filter).
        if (str_contains($field_key, 'attr_')) {
          $item->getField($field_key)->setValues([]);
        }
      }
    }

    $item->getField('stock')->setValues([$in_stock]);
  }

  /**
   * Helper function to get stock status for index item.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   *
   * @return int
   *   Return 2 if product is buyable or in-stock else 0.
   */
  public function getStockStatusForIndex(SKUInterface $sku) {
    if (!alshaya_acm_product_is_buyable($sku) || $this->isProductInStock($sku)) {
      return 2;
    }
    return 0;
  }

  /**
   * Process index item for configurable product.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param \Drupal\search_api\Item\ItemInterface $item
   *   Index item.
   * @param string $product_color
   *   Product color.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function processIndexItemConfigurable(SKUInterface $sku, ItemInterface $item, $product_color) {
    $attributes = $this->getConfigurableAttributesData($sku, $product_color);

    // Load all item fields.
    $itemFields = $item->getFields();

    // Set gathered data into parent.
    foreach ($attributes as $key => $values) {
      $field_key = 'attr_' . $key;

      // There is an issue with color field in indexes.
      // It is color in solr and attr_color in database index.
      // For all other fields it is attr_field in both indexes.
      if (isset($itemFields[$field_key])) {
        $item->getField($field_key)->setValues(array_keys($values));
      }
      elseif (isset($itemFields[$key])) {
        $item->getField($key)->setValues(array_keys($values));
      }
    }
  }

  /**
   * Helper method to get attributes for configurable product.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param string $product_color
   *   Product color.
   *
   * @return array
   *   Return associative array with attributes data.
   *   keys:
   *   - is_product_in_stock: Is product in stock status.
   *   - has_color_data: Has sku color data.
   *   - data: Associative array contains configurable attributes with array of
   *     labels.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getConfigurableAttributesData(SKUInterface $sku, $product_color) {
    $is_product_in_stock = $this->isProductInStock($sku);

    if (!$is_product_in_stock && $product_color) {
      throw new \Exception('Product not in stock, not indexing color node');
    }

    $data = [];
    $has_color_data = FALSE;
    $children = $this->getAvailableChildren($sku) ?? [];

    $configurable_attributes = $this->getConfigurableAttributes($sku);

    $sizeGroupingEnabled = $this->configFactory->get('alshaya_acm_product.settings')->get('enable_size_grouping_filter');
    // Gather data from children to set in parent.
    foreach ($children as $child) {
      $child_color = $this->getPdpSwatchValue($child, $configurable_attributes);

      // Need to have a flag to avoid indexing main node when it has colors.
      // For nodes not having swatch/color attribute, we still need to index it.
      if (!empty($child_color)) {
        $has_color_data = TRUE;
      }

      // Avoid all products of different color when indexing product color node.
      if ($product_color && $child_color !== $product_color) {
        continue;
      }

      // Do not add data from child to parent if language do not match.
      if ($child->language()->getId() != $sku->language()->getId()) {
        continue;
      }

      // Loop through the indexable fields.
      foreach ($this->getAttributesToIndex() as $key => $field) {
        $field_key = 'attr_' . $key;
        $field_data = $child->get($field_key)->getValue();
        // We don't need values from child sku.
        // Express delivery attribute will be considered at parent level.
        if ($field_key == 'attr_express_delivery') {
          continue;
        }
        if (!empty($field_data)) {
          $size_group = '';
          if ($field_key == 'attr_size' && $sizeGroupingEnabled) {
            $size_group = $child->get('attr_size_group_code')->getString();
            // Group all the sizes without group in a section in bottom.
            $size_group = $size_group ?: 'other';
          }

          foreach ($field_data as $field_value) {
            if (!empty($size_group)) {
              $value = $size_group . self::SIZE_GROUP_SEPARATOR . $field_value['value'];
              $data[$key][$value] = $value;
            }
            else {
              $data[$key][$field_value['value']] = $field_value['value'];
            }
          }
        }
      }
    }

    // Loop through the indexable fields for parent.
    // If we get data at parent level, let's index that too.
    foreach ($this->getAttributesToIndex() as $key => $field) {
      $field_key = 'attr_' . $key;
      $field_data = $sku->get($field_key)->getValue();

      if (!empty($field_data)) {
        $size_group = '';
        if ($field_key == 'attr_size' && $sizeGroupingEnabled) {
          $size_group = $sku->get('attr_size_group_code')->getString();
          // Group all the sizes without group in a section in bottom.
          $size_group = $size_group ?: 'other';
        }
        foreach ($field_data as $field_value) {
          if (!empty($size_group)) {
            $value = $size_group . self::SIZE_GROUP_SEPARATOR . $field_value['value'];
            $data[$key][$value] = $value;
          }
          else {
            $data[$key][$field_value['value']] = $field_value['value'];
          }
        }
      }
    }

    // We do not index for color node with no variant in stock.
    if ($product_color && empty($children)) {
      throw new \Exception('No valid children found for color ' . $product_color);
    }

    // Do not index main parent if product is in stock and has color data.
    $mode = $this->getListingDisplayMode();
    if ($mode === self::NON_AGGREGATED_LISTING && $is_product_in_stock && empty($product_color) && $has_color_data) {
      // We use the code 200 as it is normal with the configuration.
      throw new \Exception('Product has color, we do not index main node when doing group by color', 200);
    }

    return $data;
  }

  /**
   * Get the configurable fields we want to capture separately as fields.
   */
  public function getAttributesToIndex() {
    static $indexFields;

    if (isset($indexFields)) {
      return $indexFields;
    }

    $fields = $this->skuFieldsManager->getFieldAdditions();
    $indexFields = array_filter($fields, fn($field) => !empty($field['index']));

    return $indexFields;
  }

  /**
   * Get description for a sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Description of the product.
   */
  public function getDescription(SKUInterface $sku, $context) {
    return $this->productInfoHelper->getValue($sku, 'description', $context);
  }

  /**
   * Get description for a sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Description of the product.
   */
  public function getShortDescription(SKUInterface $sku, $context) {
    return $this->productInfoHelper->getValue($sku, 'short_description', $context);
  }

  /**
   * Helper function to invalidate PDP page caches.
   */
  public function invalidatePdpCache($term = NULL) {
    if ($term instanceof TermInterface) {
      $nids = $this->getNodesFromTermId($term->id());
      foreach ($this->termStorage->loadTree('acq_product_category', $term->id(), NULL, TRUE) as $taxonomy_child) {
        $pdp_layout = $taxonomy_child->get('field_pdp_layout')->getString() ?? NULL;
        if ($pdp_layout == self::PDP_LAYOUT_INHERIT_KEY) {
          $nids = array_merge($nids, $this->getNodesFromTermId($taxonomy_child->id()));
        }
      }
      foreach ($nids as $nid) {
        Cache::invalidateTags([
          'node:' . $nid,
        ]);
      }
    }
    else {
      Cache::invalidateTags([
        'node_type:acq_product',
      ]);
    }
  }

  /**
   * Helper function to get all nids associated with a term.
   */
  public function getNodesFromTermId($tid = '') {
    $query = $this->connection->select('node__field_category', 'nc');
    $query->fields('nc', ['entity_id']);
    $query->condition('nc.field_category_target_id', $tid);
    $query->distinct();
    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Check if listing mode is non-aggregated.
   *
   * @return bool
   *   TRUE if listing mode is set to non-aggregated.
   */
  public function isListingModeNonAggregated() {
    return ($this->getListingDisplayMode() === self::NON_AGGREGATED_LISTING);
  }

  /**
   * Helper function to check if SKU entity has style code attribute or not.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity for which the style code needs to be fetched.
   *
   * @return bool|mixed
   *   Style code value if field & value exist, FALSE otherwise.
   */
  public function fetchStyleCode(SKU $sku) {
    if ($sku->hasField('attr_style_code') &&
      $style_code = $sku->get('attr_style_code')->getString()) {
      return $style_code;
    }

    return FALSE;
  }

  /**
   * Get selected variant id.
   *
   * @return int|null
   *   Selected variant id if available.
   */
  public function getSelectedVariantId() {
    $from_query = $this->currentRequest->query->get('selected');
    if (empty(self::$selectedVariantId) && !empty($from_query)) {
      $this->setSelectedVariantId($from_query);
    }

    return self::$selectedVariantId ?? NULL;
  }

  /**
   * Set selected variant id.
   *
   * @param int|null $id
   *   Selected variant id.
   */
  public function setSelectedVariantId($id) {
    self::$selectedVariantId = $id;
  }

  /**
   * Recursive helper function to get combination array.
   *
   * It converts ['color' => 'Black', 'size' => 'X', 'material' => 'Leather']
   * to ['color']['Black']['size']['X']['material']['Leather'] = 1.
   *
   * @param array $options
   *   One dimensional array.
   *
   * @return array
   *   Multi dimensional array.
   */
  public function getCombinationArray(array $options) {
    $combination = [];
    foreach ($options as $code => $value) {
      unset($options[$code]);

      $combination[$code][$value] = count($options) > 0
        ? $this->getCombinationArray($options)
        : 1;

      break;
    }

    return $combination;
  }

  /**
   * Wrapper function get labels and make the urls absolute.
   *
   * TO BE used for APIs.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Labels data.
   */
  public function getSkuLabels(SKUInterface $sku, string $context): array {
    // This function should be used for APIs.
    $labels = $this->getLabels($sku, $context, FALSE);

    if (empty($labels)) {
      return [];
    }

    // Convert render array to absolute image urls.
    foreach ($labels as &$label) {
      $label['image'] = is_array($label['image'])
        ? file_create_url($label['image']['#uri'])
        : $label['image'];
    }

    return $labels;
  }

  /**
   * Wrapper function get promotions.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Promotions.
   */
  public function getPromotions(SKUInterface $sku) {
    $promotions = [];
    $promotions_data = $this->getPromotionsFromSkuId($sku, '', ['cart'], 'full', FALSE);
    foreach ($promotions_data as $nid => $promotion) {
      $promotions[] = [
        'text' => $promotion['text'],
        'promo_node' => $nid,
        'promo_web_url' => str_replace('/' . $this->languageManager->getCurrentLanguage()->getId() . '/',
          '',
          Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString(TRUE)->getGeneratedUrl()),
      ];
    }
    return $promotions;
  }

  /**
   * Wrapper function to get config.
   *
   * @param string $key
   *   Config key / id.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Config.
   */
  protected function getConfig(string $key) {
    static $config = [];

    if (empty($config[$key])) {
      // Adding static cache to avoid applying overrides and other code
      // every-time we try to get config.
      $config[$key] = $this->configFactory->get($key);
    }

    return $config[$key];
  }

  /**
   * Function returns the configurable attribute names of the given sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   The sku entity.
   *
   * @return array
   *   Array of configurable attribute names.
   *
   * @todo getConfigurableCombinations() used in this method is expensive as it
   * does some complex calculations. Need to find a simpler method of doing it
   * as done in the function alshaya_acm_product_get_sku_configurable_values()
   * in https://github.com/acquia-pso/alshaya/pull/15739.
   */
  public function getConfigurableAttributeNames(SKUInterface $sku): array {
    $parent_sku = $this->getParentSkuBySku($sku);
    if (is_null($parent_sku)) {
      $parent_sku = $sku;
    }

    if ($parent_sku->getType() == 'simple') {
      return [];
    }

    $attributes = [];
    if ($attrs = $parent_sku->get('field_configurable_attributes')->first()) {
      // phpcs:ignore
      $configurable_attributes = unserialize($attrs->getString());
      if (!empty($configurable_attributes)) {
        foreach ($configurable_attributes as $attribute) {
          $attributes[] = $attribute['code'];
        }
      }
    }

    return $attributes;
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
   *   Configurable Values For Rest Apis.
   */
  public function getConfigurableValuesForApi(SKUInterface $sku, array $attributes = []): array {
    if ($sku->bundle() !== 'simple') {
      return [];
    }

    $values = $this->getConfigurableValues($sku);
    $attr_values = array_column($attributes, 'value', 'attribute_code');
    foreach ($values as $attribute_code => &$value) {
      $value['attribute_code'] = $attribute_code;
      if (isset($attr_values[str_replace('attr_', '', $attribute_code)]) && $attr_value = $attr_values[str_replace('attr_', '', $attribute_code)]) {
        $value['value'] = (string) $attr_value;
      }
    }

    return array_values($values);
  }

  /**
   * Get sanized version of sku.
   *
   * @param string $skuId
   *   Product sku id.
   *
   * @return array
   *   Return sanitized version sku
   */
  public function getSanitizedSku($skuId) {
    if (empty($skuId)) {
      return NULL;
    }

    // Remove any spcial characters,
    // Replace space with hyphen
    // e.g. Replace >M-GC58 7d780 shs%#/ /#$#@/564\46 /5653<
    // into M-GC58-7d780-shs-56446-5653.
    return preg_replace(
        ['#[\\s-]+#', '#[^A-Za-z0-9. -]+#'],
        ['-', ''],

        urldecode($skuId)
      );
  }

  /**
   * To validate EAN 13 digit barcode.
   *
   * @param bool $barcode
   *   Barcode number.
   *
   * @return bool
   *   Return a boolean value.
   */
  public function validateEanBarcode($barcode) {
    // Check to see if barcode is 13 digits long.
    if (!preg_match("/^[0-9]{13}$/", $barcode)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Utility function to get configurable options raw values.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param string $code
   *   Attribute code.
   *
   * @return array
   *   Return configurable options.
   */
  public function getConfigurableRawAttributesData(SKUInterface $sku, $code) {
    $parent_sku = $this->getParentSkuBySku($sku);
    if ($parent_sku instanceof SKUInterface) {
      $product_tree = Configurable::deriveProductTree($parent_sku);
      $options = [
        'option_id' => $product_tree['configurables'][$code]['attribute_id'] ?? '',
        'option_value' => $product_tree['combinations']['by_sku'][$sku->getSku()][$code] ?? '',
      ];
      return $options;
    }
    return NULL;
  }

}
