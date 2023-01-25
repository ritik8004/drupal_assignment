<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\ResourceResponse;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\ProductCategoryHelper;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a resource to get attributes for SKU's list.
 *
 * @RestResource(
 *   id = "skus_product_list",
 *   label = @Translation("SKUs Product List"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/skus/product-list",
 *   }
 * )
 */
class SkusProductList extends ResourceBase {

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Store cache tags and contexts to be added in response.
   *
   * @var array
   */
  private $cache;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Node Storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  private $skuImagesManager;

  /**
   * Product category helper service.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  private $productCategoryHelper;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AdvancedPageResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info helper.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\alshaya_acm_product\ProductCategoryHelper $product_category_helper
   *   The Product Category helper service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MobileAppUtility $mobile_app_utility,
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager,
    SkuManager $sku_manager,
    ProductInfoHelper $product_info_helper,
    SkuInfoHelper $sku_info_helper,
    ModuleHandlerInterface $module_handler,
    SkuImagesManager $sku_images_manager,
    ProductCategoryHelper $product_category_helper,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->skuManager = $sku_manager;
    $this->cache = [
      'tags' => [],
      'contexts' => [],
    ];
    $this->productInfoHelper = $product_info_helper;
    $this->skuInfoHelper = $sku_info_helper;
    $this->moduleHandler = $module_handler;
    $this->skuImagesManager = $sku_images_manager;
    $this->productCategoryHelper = $product_category_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('acq_sku.product_info_helper'),
      $container->get('alshaya_acm_product.sku_info'),
      $container->get('module_handler'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('alshaya_acm_product.category_helper'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing attributes of skus.
   */
  public function get() {
    $sku_list = $this->requestStack->query->get('skus');
    return $this->getSkuListData($sku_list);
  }

  /**
   * Helper function to get data for sku list.
   */
  protected function getSkuListData($sku_list) {
    if (empty($sku_list)) {
      $this->logger->error('No Products are selected hence cannot find corresponding Sku\'s');
      return $this->mobileAppUtility->sendStatusResponse($this->t('No Products are selected hence cannot find corresponding SKUs'));
    }
    $skus = explode(',', $sku_list);
    $data = [];
    $sku_cache_tags = [];
    $with_parent_details = (bool) $this->requestStack->query->get('with_parent_details');

    foreach ($skus as $value) {
      $skuEntity = SKU::loadFromSku($value);
      if (($skuEntity instanceof SKUInterface)) {
        $data[] = $this->getSkuData($skuEntity, '', $with_parent_details);
        $sku_cache_tags[] = $skuEntity->getCacheTags();
      }
      else {
        // API expects NULL if SKU is not present in the system.
        $data[] = NULL;
      }
    }

    $response = new ResourceResponse($data);

    // Add cacheability metadata.
    $cacheable_metadata = $response->getCacheableMetadata();
    foreach ($sku_cache_tags as $sku_cache_tag) {
      $cacheable_metadata->addCacheTags($sku_cache_tag);
    }

    // Set max-age if API request contains invalid/disabled SKUs.
    if (in_array(NULL, $data)) {
      $mobile_app_settings = $this->configFactory->get('alshaya_mobile_app.settings');
      $max_age = $mobile_app_settings->get('no_product_cache_ttl');
      $this->cache['tags'] = Cache::mergeTags($this->cache['tags'], $mobile_app_settings->getCacheTags());

      $cacheable_metadata->mergeCacheMaxAge($max_age);
    }

    if (!empty($this->cache['tags'])) {
      $cacheable_metadata->addCacheTags($this->cache['tags']);
    }
    if (!empty($this->cache['contexts'])) {
      $cacheable_metadata->addCacheContexts($this->cache['contexts']);
    }
    // Since the sku list is passed in query arguments, we shall add a
    // dependency on query arguments.
    $cacheable_metadata->addCacheContexts(['url.query_args']);

    $response->addCacheableDependency($cacheable_metadata);

    return $response;
  }

  /**
   * Wrapper function to get product data.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $link
   *   Product link if main product.
   * @param bool $with_parent_details
   *   Flag to identify whether to get parent details or not.
   *
   * @return array
   *   Product Data.
   */
  private function getSkuData(SKUInterface $sku, string $link = '', bool $with_parent_details = FALSE): array {
    /** @var \Drupal\acq_sku\Entity\SKU $sku */
    $data = [];

    $this->cache['tags'] = Cache::mergeTags($this->cache['tags'], $sku->getCacheTags());
    $this->cache['contexts'] = Cache::mergeTags($this->cache['contexts'], $sku->getCacheContexts());

    $data['id'] = (int) $sku->id();
    $data['sku'] = $sku->getSku();
    if ($link) {
      $data['link'] = $link;
    }
    $parent_sku = $this->skuManager->getParentSkuBySku($sku);
    $data['parent_sku'] = $parent_sku ? $parent_sku->getSku() : NULL;
    $data['title'] = (string) $this->productInfoHelper->getTitle($sku, 'pdp');

    $prices = $this->skuManager->getMinPrices($sku);
    $data['original_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $prices['price']);
    $data['final_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $prices['final_price']);

    $stockInfo = $this->skuInfoHelper->stockInfo($sku);
    $data['stock'] = $stockInfo['stock'];
    $data['in_stock'] = $stockInfo['in_stock'];
    $data['max_sale_qty'] = $stockInfo['max_sale_qty'];

    if ($with_parent_details === TRUE) {
      $plugin = $sku->getPluginInstance();
      $data['parent_max_sale_qty'] = $parent_sku ? (int) $plugin->getMaxSaleQty($parent_sku) : NULL;
    }

    $data['delivery_options'] = [
      'home_delivery' => [],
      'click_and_collect' => [],
    ];
    $data['flags'] = [];
    $data['delivery_options'] = NestedArray::mergeDeepArray([
      $this->getDeliveryOptionsStatus($sku),
      $data['delivery_options'],
    ], TRUE);
    $data['flags'] = NestedArray::mergeDeepArray([
      alshaya_acm_product_get_flags_config(),
      alshaya_acm_product_get_flags_status($sku),
    ], TRUE);

    $images = $this->skuImagesManager->getProductMediaDataWithStyles($sku, 'pdp');
    $data = array_merge($data, $images);
    $data['attributes'] = $this->skuInfoHelper->getAttributes($sku);
    $data['promotions'] = $this->getPromotions($sku);
    $promo_label = $this->skuManager->getDiscountedPriceMarkup($data['original_price'], $data['final_price']);
    if ($promo_label) {
      $data['promotions'][] = [
        'text' => $promo_label,
      ];
    }
    $data['configurable_values'] = $this->skuManager->getConfigurableValuesForApi($sku);
    $data['configurable_attributes'] = $this->skuManager->getConfigurableAttributeNames($sku);
    $data['labels'] = $this->skuManager->getSkuLabels($sku, 'plp');
    $data['categorisations'] = [];
    if (!$this->skuManager->isSkuFreeGift($sku)) {
      $node = $this->skuManager->getDisplayNode($sku);
      if ($node) {
        $data['categorisations'] = $this->productCategoryHelper->getSkuCategorisations($node);
      }
    }
    $this->moduleHandler->alter('alshaya_mobile_app_skus_product_list_data', $data, $sku, $with_parent_details);
    return $data;
  }

  /**
   * Wrapper function to get media items for an SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Media Items.
   */
  private function getDeliveryOptionsStatus(SKUInterface $sku) {
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $this->cache['tags'] = Cache::mergeTags(
      $this->cache['tags'],
      $this->configFactory->get('alshaya_click_collect.settings')->getCacheTags(),
      $this->configFactory->get('alshaya_acm_product.settings')->getCacheTags()
    );

    return [
      'home_delivery' => [
        'status' => alshaya_acm_product_is_buyable($sku) && alshaya_acm_product_available_home_delivery($sku),
      ],
      'click_and_collect' => [
        'status' => alshaya_acm_product_available_click_collect($sku),
      ],
    ];
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
  private function getPromotions(SKUInterface $sku): array {
    $promotions = [];
    $promotions_data = $this->skuManager->getPromotionsFromSkuId($sku, '', ['cart'], 'full', TRUE, 'app');
    foreach ($promotions_data as $nid => $promotion) {
      if (is_numeric($nid)) {
        $this->cache['tags'][] = 'node:' . $nid;
        $promotion_node = $this->nodeStorage->load($nid);
        $promotions[] = [
          'text' => $promotion['text'],
          'deeplink' => $this->mobileAppUtility->getDeepLink($promotion_node, 'promotion'),
        ];
      }
    }
    return $promotions;
  }

}
