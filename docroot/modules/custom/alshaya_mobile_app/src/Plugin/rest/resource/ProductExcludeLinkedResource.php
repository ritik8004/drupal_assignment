<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\taxonomy\TermInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\alshaya_acm_product\ProductCategoryHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_product_options\ProductOptionsHelper;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a resource to get product details excluding linked products.
 *
 * @RestResource(
 *   id = "product_exclude_linked",
 *   label = @Translation("Product Excluded Linked"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/product-exclude-linked/{sku}"
 *   }
 * )
 */
class ProductExcludeLinkedResource extends ResourceBase {

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  private $skuImagesManager;

  /**
   * Product Info helper.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  private $productInfoHelper;

  /**
   * Node Storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * Term Storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  private $termStorage;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  private $mobileAppUtility;

  /**
   * Production Options Manager service object.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  protected $productOptionsManager;

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
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * Product category helper service.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  protected $productCategoryHelper;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Product Options Helper.
   *
   * @var \Drupal\alshaya_product_options\ProductOptionsHelper
   */
  protected $optionsHelper;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ProductResource constructor.
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
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Production Options Manager service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\alshaya_acm_product\ProductCategoryHelper $product_category_helper
   *   The Product Category helper service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\alshaya_product_options\ProductOptionsHelper $options_helper
   *   Product Options Helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    SkuManager $sku_manager,
    SkuImagesManager $sku_images_manager,
    ProductInfoHelper $product_info_helper,
    EntityTypeManagerInterface $entity_type_manager,
    MobileAppUtility $mobile_app_utility,
    ProductOptionsManager $product_options_manager,
    ModuleHandlerInterface $module_handler,
    SkuInfoHelper $sku_info_helper,
    ProductCategoryHelper $product_category_helper,
    RequestStack $request_stack,
    ProductOptionsHelper $options_helper,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->productInfoHelper = $product_info_helper;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->mobileAppUtility = $mobile_app_utility;
    $this->productOptionsManager = $product_options_manager;
    $this->cache = [
      'tags' => [],
      'contexts' => [],
    ];
    $this->moduleHandler = $module_handler;
    $this->skuInfoHelper = $sku_info_helper;
    $this->productCategoryHelper = $product_category_helper;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->optionsHelper = $options_helper;
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
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('acq_sku.product_info_helper'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('acq_sku.product_options_manager'),
      $container->get('module_handler'),
      $container->get('alshaya_acm_product.sku_info'),
      $container->get('alshaya_acm_product.category_helper'),
      $container->get('request_stack'),
      $container->get('alshaya_product_options.helper'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns available delivery method data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing delivery methods data.
   */
  public function get(string $sku) {
    $node = NULL;
    $skuEntity = SKU::loadFromSku($sku);

    if (!($skuEntity instanceof SKUInterface)) {
      throw (new NotFoundHttpException());
    }

    $link = '';
    if (!$this->skuManager->isSkuFreeGift($skuEntity)) {
      $node = $this->skuManager->getDisplayNode($sku);
      if (!($node instanceof NodeInterface)) {
        throw (new NotFoundHttpException());
      }

      $link = $node->toUrl('canonical', ['absolute' => TRUE])
        ->toString(TRUE)
        ->getGeneratedUrl();
    }

    $with_parent_details = (bool) $this->requestStack->query->get('with_parent_details');

    $data = $this->getSkuData($skuEntity, $link, $with_parent_details);
    $data['delivery_options'] = NestedArray::mergeDeepArray([
      $this->getDeliveryOptionsConfig($skuEntity),
      $data['delivery_options'],
    ], TRUE);
    $data['flags'] = NestedArray::mergeDeepArray([
      alshaya_acm_product_get_flags_config(),
      $data['flags'],
    ], TRUE);

    if (!$this->skuManager->isSkuFreeGift($skuEntity)) {
      $data['categorisations'] = $this->productCategoryHelper->getSkuCategorisations($node);
    }

    $data['configurable_attributes'] = $this->skuManager->getConfigurableAttributeNames($skuEntity);

    // Allow other modules to alter product data.
    // For simple sku this hook is called where
    // process_swatch_for_grouping_attributes is checked
    // to process the grouped_variants.
    $this->moduleHandler->alter('alshaya_mobile_app_product_exclude_linked_data', $data, $skuEntity, $with_parent_details);
    if (isset($data['grouping_attribute_with_swatch'])) {
      $data['grouped_variants'] = $this->getGroupedVariants($data, $with_parent_details);
    }
    $response = new ResourceResponse($data);
    $cacheableMetadata = $response->getCacheableMetadata();

    if (!empty($this->cache['contexts'])) {
      $cacheableMetadata->addCacheContexts($this->cache['contexts']);
    }

    if (!empty($this->cache['tags'])) {
      $cacheableMetadata->addCacheTags($this->cache['tags']);
    }

    $cacheableMetadata->addCacheContexts(['url.query_args']);

    $response->addCacheableDependency($cacheableMetadata);

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
    AlshayaRequestContextManager::updateDefaultContext('app');

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

    if ($sku->get('attr_brand_logo')->getString()) {
      $data['brand_logo'] = $this->getBrandLogo($sku);
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
      alshaya_acm_product_get_flags_status($sku),
      $data['flags'],
    ], TRUE);

    $media_contexts = [
      'pdp' => 'detail',
      'search' => 'listing',
      'teaser' => 'teaser',
    ];
    foreach ($media_contexts as $key => $context) {
      $data['media'][] = [
        'context' => $context,
        'media' => $this->skuImagesManager->getProductMediaDataWithStyles($sku, $key),
      ];
    }

    $label_contexts = [
      'pdp' => 'detail',
      'plp' => 'listing',
    ];
    foreach ($label_contexts as $key => $context) {
      $data['labels'][] = [
        'context' => $context,
        'labels' => $this->skuManager->getSkuLabels($sku, $key),
      ];
    }
    $data['attributes'] = $this->skuInfoHelper->getAttributes($sku);
    $data['promotions'] = $this->getPromotions($sku);
    $data['configurable_values'] = $this->skuManager->getConfigurableValuesForApi($sku);
    if (!empty($data['configurable_values'])) {
      $data['configurable_values'] = array_map(function ($option) {
        $option['option_id'] = (int) $option['option_id'];
        return $option;
      }, $data['configurable_values']);
    }

    if ($sku->bundle() === 'configurable') {
      $data['swatch_data'] = $this->getSwatchData($sku);
      if (!empty($data['swatch_data']['swatches'])) {
        $data['swatch_data']['swatches'] = array_map(function ($swatch) {
          $swatch['child_sku_code'] = strval($swatch['child_sku_code']);
          return $swatch;
        }, $data['swatch_data']['swatches']);
      }

      $data['cart_combinations'] = $this->getConfigurableCombinations($sku);
      foreach ($data['cart_combinations']['by_sku'] ?? [] as $values) {
        $child = SKU::loadFromSku($values['sku']);
        if (!$child instanceof SKUInterface) {
          continue;
        }
        $variant = $this->getSkuData($child, '', $with_parent_details);
        $variant['configurable_values'] = $this->skuManager->getConfigurableValuesForApi($child, $values['attributes']);
        if (!empty($variant['configurable_values'])) {
          $variant['configurable_values'] = array_map(function ($variant_option) {
            $variant_option['option_id'] = (int) $variant_option['option_id'];
            return $variant_option;
          }, $variant['configurable_values']);
        }

        $data['variants'][] = $variant;
      }

      $data['swatch_data'] = $data['swatch_data'] ?: new \stdClass();
      $data['cart_combinations'] = $data['cart_combinations'] ?: new \stdClass();
    }

    if ($this->skuManager->isSkuFreeGift($sku)) {
      // Allow other modules to alter light product data.
      $type = 'full';
      $this->moduleHandler->alter('alshaya_acm_product_gift_product_data', $sku, $data, $type);
    }
    else {
      // Allow other modules to alter light product data.
      $type = 'full';
      $this->moduleHandler->alter('alshaya_acm_product_light_product_data', $sku, $data, $type);
    }

    return $data;
  }

  /**
   * Get grouped products for pdp based on grouping attribute.
   *
   * @param array $data
   *   Array of product data.
   * @param bool $with_parent_details
   *   Flag to identify whether to get parent details or not.
   *
   * @return array
   *   Grouping products for pdp.
   */
  private function getGroupedVariants(array &$data, bool $with_parent_details) {
    $grouped_variants = [];
    if (!empty($data['grouped_variants'])) {
      foreach ($data['grouped_variants'] as $grouped_sku) {
        if (!$grouped_sku instanceof SKUInterface) {
          continue;
        }
        $variant = $this->getSkuData($grouped_sku, '', $with_parent_details);
        if (isset($data['grouped_variants'][$grouped_sku->getSku()]['attributes'])) {
          $variant['attributes'] = $data['grouped_variants'][$grouped_sku->getSku()]['attributes'];
        }
        $grouped_variants[] = $variant;
      }
    }
    return $grouped_variants;
  }

  /**
   * Get delivery options for pdp.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Delivery options for pdp.
   */
  private function getDeliveryOptionsConfig(SKUInterface $sku) {
    return [
      'home_delivery' => alshaya_acm_product_get_home_delivery_config(),
      'click_and_collect' => alshaya_click_collect_get_config(),
    ];
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
   * Wrapper function get the brand logo.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return string
   *   brand logo.
   */
  private function getBrandLogo(SKUInterface $sku) {
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $logo = alshaya_acm_product_get_brand_logo($sku);
    if (empty($logo)) {
      return '';
    }
    return file_create_url($logo['#uri']);
  }

  /**
   * Wrapper function get swatches data.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Swatches Data.
   */
  private function getSwatchData(SKUInterface $sku): array {
    $swatches = $this->skuImagesManager->getSwatchData($sku);

    if (isset($swatches['swatches'])) {
      $swatches['swatches'] = array_values($swatches['swatches']);
    }

    return $swatches;
  }

  /**
   * Wrapper function get configurable combinations.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Configurable combinations.
   */
  private function getConfigurableCombinations(SKUInterface $sku): array {
    $combinations = $this->skuManager->getConfigurableCombinations($sku);
    unset($combinations['by_attribute']);

    foreach ($combinations['by_sku'] ?? [] as $child_sku => $attributes) {
      $combinations['by_sku'][$child_sku] = [
        'sku' => strval($child_sku),
      ];

      foreach ($attributes as $attribute_code => $value) {
        $combinations['by_sku'][$child_sku]['attributes'][] = [
          'attribute_code' => $attribute_code,
          'value' => (int) $value,
        ];
      }
    }

    $size_labels = $this->skuInfoHelper->getSizeLabels($sku);

    foreach ($combinations['attribute_sku'] ?? [] as $attribute_code => $attribute_data) {
      $combinations['attribute_sku'][$attribute_code] = [
        'attribute_code' => $attribute_code,
      ];

      foreach ($attribute_data as $value => $skus) {
        $attr_value = [
          'value' => $value,
          'skus' => array_map('strval', $skus),
        ];

        // Labels for all attribute codes.
        $attr_value['label'] = $size_labels[$value] ?? '';
        if (empty($attr_value['label'])) {
          $term = $this->productOptionsManager->loadProductOptionByOptionId($attribute_code, $value, $this->mobileAppUtility->currentLanguage());
          if ($term instanceof TermInterface) {
            $attr_value['label'] = $term->label();
          }
        }

        $combinations['attribute_sku'][$attribute_code]['values'][] = $attr_value;
        // Key to flag size label.
        $combinations['attribute_sku'][$attribute_code]['showLabel'] = FALSE;
        $alternates = $this->optionsHelper->getSizeGroup($attribute_code);
        if ($alternates) {
          $combinations['attribute_sku'][$attribute_code]['showLabel'] = TRUE;
        }
      }
    }

    foreach ($combinations as $key => $value) {
      $combinations[$key] = array_values($value);
    }

    return $combinations;
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
      $this->cache['tags'][] = 'node:' . $nid;
      $promotion_node = $this->nodeStorage->load($nid);
      $promotions[] = [
        'text' => $promotion['text'],
        'deeplink' => $this->mobileAppUtility->getDeepLink($promotion_node, 'promotion'),
      ];
    }
    return $promotions;
  }

}
