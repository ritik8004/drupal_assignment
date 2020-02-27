<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
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

/**
 * Provides a resource to get product details excliding linked products.
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
    SkuInfoHelper $sku_info_helper
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
      $container->get('alshaya_acm_product.sku_info')
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
    $skuEntity = SKU::loadFromSku($sku);

    if (!($skuEntity instanceof SKUInterface)) {
      throw (new NotFoundHttpException());
    }

    $node = $this->skuManager->getDisplayNode($sku);
    if (!($node instanceof NodeInterface)) {
      throw (new NotFoundHttpException());
    }

    $link = $node->toUrl('canonical', ['absolute' => TRUE])
      ->toString(TRUE)
      ->getGeneratedUrl();

    $data = $this->getSkuData($skuEntity, $link);

    $data['delivery_options'] = NestedArray::mergeDeepArray([$this->getDeliveryOptionsConfig($skuEntity), $data['delivery_options']], TRUE);
    $response = new ResourceResponse($data);
    $cacheableMetadata = $response->getCacheableMetadata();

    if (!empty($this->cache['contexts'])) {
      $cacheableMetadata->addCacheContexts($this->cache['contexts']);
    }

    if (!empty($this->cache['tags'])) {
      $cacheableMetadata->addCacheTags($this->cache['tags']);
    }

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
   *
   * @return array
   *   Product Data.
   */
  private function getSkuData(SKUInterface $sku, string $link = ''): array {
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
    $data['delivery_options'] = [
      'home_delivery' => [],
      'click_and_collect' => [],
    ];
    $data['delivery_options'] = NestedArray::mergeDeepArray([$this->getDeliveryOptionsStatus($sku), $data['delivery_options']], TRUE);

    $media_contexts = [
      'pdp' => 'detail',
      'search' => 'listing',
      'teaser' => 'teaser',
    ];
    foreach ($media_contexts as $key => $context) {
      $data['media'][] = [
        'context' => $context,
        'media' => $this->skuInfoHelper->getMedia($sku, $key),
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
    $promo_label = $this->skuManager->getDiscountedPriceMarkup($data['original_price'], $data['final_price']);
    if ($promo_label) {
      $data['promotions'][] = [
        'text' => $promo_label,
      ];
    }

    $data['configurable_values'] = $this->getConfigurableValues($sku);

    if ($sku->bundle() === 'configurable') {
      $data['swatch_data'] = $this->getSwatchData($sku);
      $data['cart_combinations'] = $this->getConfigurableCombinations($sku);

      foreach ($data['cart_combinations']['by_sku'] ?? [] as $values) {
        $child = SKU::loadFromSku($values['sku']);
        if (!$child instanceof SKUInterface) {
          continue;
        }
        $variant = $this->getSkuData($child);
        $variant['configurable_values'] = $this->getConfigurableValues($child, $values['attributes']);
        $data['variants'][] = $variant;
      }

      $data['swatch_data'] = $data['swatch_data']?: new \stdClass();
      $data['cart_combinations'] = $data['cart_combinations']?: new \stdClass();
    }

    return $data;
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
  private function getConfigurableValues(SKUInterface $sku, array $attributes = []): array {
    if ($sku->bundle() !== 'simple') {
      return [];
    }

    $values = $this->skuManager->getConfigurableValues($sku);
    $attr_values = array_column($attributes, 'value', 'attribute_code');
    foreach ($values as $attribute_code => &$value) {
      $value['attribute_code'] = $attribute_code;
      if ($attr_value = $attr_values[str_replace('attr_', '', $attribute_code)]) {
        $value['value'] = (string) $attr_value;
      }
    }

    return array_values($values);
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
        'sku' => $child_sku,
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
          'skus' => $skus,
        ];

        if ($attribute_code == 'size') {
          if (!empty($size_labels[$value])) {
            $attr_value['label'] = $size_labels[$value];
          }
          elseif (
            ($term = $this->productOptionsManager->loadProductOptionByOptionId(
              $attribute_code,
              $value,
              $this->mobileAppUtility->currentLanguage())
            )
            && $term instanceof TermInterface
          ) {
            $attr_value['label'] = $term->label();
          }
        }

        $combinations['attribute_sku'][$attribute_code]['values'][] = $attr_value;
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
    $promotions_data = $this->skuManager->getPromotionsFromSkuId($sku, '', ['cart'], 'full');
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
