<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\Core\Url;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\NodeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\taxonomy\TermInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\alshaya_acm_product\ProductCategoryHelper;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_product_options\ProductOptionsHelper;

/**
 * Provides a resource to get product details.
 *
 * @RestResource(
 *   id = "product",
 *   label = @Translation("Product"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/product/{sku}"
 *   }
 * )
 */
class ProductResource extends ResourceBase {

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
   * Production Options Manager service object.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  private $productOptionsManager;

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
  private $moduleHandler;

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  private $skuInfoHelper;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Product category helper service.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  protected $productCategoryHelper;

  /**
   * Product Options Helper.
   *
   * @var \Drupal\alshaya_product_options\ProductOptionsHelper
   */
  private $optionsHelper;

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
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Production Options Manager service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\alshaya_acm_product\ProductCategoryHelper $product_category_helper
   *   The Product Category helper service.
   * @param \Drupal\alshaya_product_options\ProductOptionsHelper $options_helper
   *   Product Options Helper.
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
    ProductOptionsManager $product_options_manager,
    ModuleHandlerInterface $module_handler,
    SkuInfoHelper $sku_info_helper,
    LanguageManagerInterface $language_manager,
    RequestStack $request_stack,
    ProductCategoryHelper $product_category_helper,
    ProductOptionsHelper $options_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->productInfoHelper = $product_info_helper;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->productOptionsManager = $product_options_manager;
    $this->cache = [
      'tags' => [],
      'contexts' => [],
    ];
    $this->moduleHandler = $module_handler;
    $this->skuInfoHelper = $sku_info_helper;
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack;
    $this->productCategoryHelper = $product_category_helper;
    $this->optionsHelper = $options_helper;
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
      $container->get('acq_sku.product_options_manager'),
      $container->get('module_handler'),
      $container->get('alshaya_acm_product.sku_info'),
      $container->get('language_manager'),
      $container->get('request_stack'),
      $container->get('alshaya_acm_product.category_helper'),
      $container->get('alshaya_product_options.helper')
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
    if (!($node instanceof NodeInterface) && !$this->skuManager->isSkuFreeGift($skuEntity)) {
      throw (new NotFoundHttpException());
    }

    $link = $node
      ? $node->toUrl('canonical', ['absolute' => TRUE])
        ->toString(TRUE)
        ->getGeneratedUrl()
      : '';

    $data = $this->getSkuData($skuEntity, $link);

    $data['delivery_options'] = NestedArray::mergeDeepArray([
      $this->getDeliveryOptionsConfig($skuEntity),
      $data['delivery_options'],
    ], TRUE);
    $data['flags'] = NestedArray::mergeDeepArray([
      alshaya_acm_product_get_flags_config(),
      $data['flags'],
    ], TRUE);

    // Allow other modules to alter product data.
    $this->moduleHandler->alter('sku_product_info', $data, $skuEntity);

    $response = new ResourceResponse($data);
    $cacheableMetadata = $response->getCacheableMetadata();

    if (!empty($this->cache['contexts'])) {
      $cacheableMetadata->addCacheContexts($this->cache['contexts']);
    }

    // Add query_args cache context.
    $cacheableMetadata->addCacheContexts(['url.query_args']);

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
    $current_request = $this->requestStack->getCurrentRequest();
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
    $data['max_sale_qty_parent'] = FALSE;
    // If parent's is marked as out of stock, even children are not available.
    // We check paren't flag if child is in-stock.
    if ($data['in_stock'] && $parent_sku instanceof SKUInterface) {
      $parentStockInfo = $this->skuInfoHelper->stockInfo($parent_sku);
      if (!($parentStockInfo['in_stock'])) {
        $data['in_stock'] = FALSE;
      }

      if (!empty($parentStockInfo['max_sale_qty'])) {
        $data['max_sale_qty'] = $parentStockInfo['max_sale_qty'];
        $data['max_sale_qty_parent'] = TRUE;
      }
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

    if ($current_request->query->get('context') != 'cart') {
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

      // Brand logo data.
      $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
      $brand_logo = alshaya_acm_product_get_brand_logo($sku);
      if ($brand_logo) {
        $data['brand_logo']['image'] = file_create_url($brand_logo['#uri']);
        $data['brand_logo']['alt'] = file_create_url($brand_logo['#alt']);
        $data['brand_logo']['title'] = file_create_url($brand_logo['#title']);
      }
    }

    $data['attributes'] = $this->skuInfoHelper->getAttributes($sku);

    $data['promotions'] = $this->getPromotions($sku);
    $promo_label = $this->skuManager->getDiscountedPriceMarkup($data['original_price'], $data['final_price']);
    if ($promo_label) {
      $data['promotions'][] = [
        'text' => $promo_label,
      ];
    }

    $data['configurable_values'] = $this->skuManager->getConfigurableValuesForApi($sku);

    if ($current_request->query->get('pdp') == 'magazinev2') {
      // Set cart image.
      $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
      $image = alshaya_acm_get_product_display_image($sku, SkuImagesHelper::STYLE_PRODUCT_THUMBNAIL, 'cart');
      // Prepare image style url.
      if (!empty($image['#uri']) && !empty($image['#theme'])) {
        // If image has image_style theme
        // then get style url by loading image style,
        // else get PIMS url directly from uri.
        $image = ($image['#theme'] == 'image_style')
          ? file_url_transform_relative(ImageStyle::load($image['#style_name'])->buildUrl($image['#uri']))
          : $image['#uri'];
      }
      $data['cart_image'] = is_string($image) ? $image : '';
    }

    if ($sku->bundle() === 'configurable') {

      if ($current_request->query->get('context') != 'cart') {
        $data['swatch_data'] = $this->getSwatchData($sku);
        $data['cart_combinations'] = $this->getConfigurableCombinations($sku);

        foreach ($data['cart_combinations']['by_sku'] ?? [] as $values) {
          $child = SKU::loadFromSku($values['sku']);
          if (!$child instanceof SKUInterface) {
            continue;
          }
          $variant = $this->getSkuData($child);
          $variant['configurable_values'] = $this->skuManager->getConfigurableValuesForApi($child, $values['attributes']);
          $data['variants'][] = $variant;

          if ($current_request->query->get('pdp') == 'magazinev2') {
            $data['variants'][$values['sku']] = $variant;

            // Set cart image.
            $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
            $image = alshaya_acm_get_product_display_image($child, SkuImagesHelper::STYLE_PRODUCT_THUMBNAIL, 'cart');
            // Prepare image style url.
            if (!empty($image['#uri'])) {
              // If image has image_style theme
              // then get style url by loading image style,
              // else get PIMS url directly from uri.
              $image = ($image['#theme'] == 'image_style')
                ? file_url_transform_relative(ImageStyle::load($image['#style_name'])->buildUrl($image['#uri']))
                : $image['#uri'];
            }
            $data['variants'][$values['sku']]['cart_image'] = is_string($image) ? $image : '';
          }
        }
      }

      $data['swatch_data'] = $data['swatch_data'] ?: new \stdClass();
      $data['cart_combinations'] = $data['cart_combinations'] ?: new \stdClass();

      if ($current_request->query->get('pdp') == 'magazinev2') {
        // Setting configurable combination array.
        $options = [];
        $size_values = [];
        $product_tree = Configurable::deriveProductTree($sku);
        $combinations = $product_tree['combinations'];
        $product_tree['configurables'] = $this->disableUnavailableOptions($sku, $product_tree['configurables']);
        $swatch_processed = FALSE;

        if ($data['in_stock']) {
          $data['configurableCombinations'][$data['sku']]['bySku'] = $combinations['by_sku'];
          $data['configurableCombinations'][$data['sku']]['byAttribute'] = $combinations['by_attribute'];
        }

        $data['configurableCombinations'][$data['sku']]['configurables'] = $product_tree['configurables'];
        // Prepare group and swatch attributes.
        foreach ($product_tree['configurables'] as $key => $configurable) {
          $data['configurableCombinations'][$data['sku']]['configurables'][$key]['isGroup'] = FALSE;
          $data['configurableCombinations'][$data['sku']]['configurables'][$key]['isSwatch'] = FALSE;
          if (!$swatch_processed && in_array($key, $this->skuManager->getPdpSwatchAttributes())) {
            $swatch_processed = TRUE;
            $data['configurableCombinations'][$data['sku']]['configurables'][$key]['isSwatch'] = TRUE;
            foreach ($configurable['values'] as $value => $label) {
              $value_id = $label['value_id'];
              if (empty($value_id)) {
                continue;
              }

              $swatch_sku = $this->skuManager->getChildSkuFromAttribute($sku, $key, $value_id);
              if ($swatch_sku instanceof SKU) {
                $swatch_image_url = $this->skuImagesManager->getPdpSwatchImageUrl($swatch_sku);
                if ($swatch_image_url) {
                  $swatch_image = file_url_transform_relative($swatch_image_url);
                  $data['configurableCombinations'][$data['sku']]['configurables'][$key]['values'][$value]['swatch_image'] = $swatch_image;
                }
              }
            }
          }
          elseif ($alternates = $this->optionsHelper->getSizeGroup($key)) {
            $data['configurableCombinations'][$data['sku']]['configurables'][$key]['isGroup'] = TRUE;
            $data['configurableCombinations'][$data['sku']]['configurables'][$key]['alternates'] = $alternates;
            $combinations = $this->skuManager->getConfigurableCombinations($sku);
            foreach ($configurable['values'] as $value => $label) {
              $value_id = $label['value_id'];
              foreach ($combinations['attribute_sku'][$key][$value_id] ?? [] as $child_sku_code) {
                $child_sku = SKU::loadFromSku($child_sku_code, $sku->language()->getId());

                if (!($child_sku instanceof SKU)) {
                  continue;
                }

                $size_values[$value][$value_id] = $this->getAlternativeValues($alternates, $child_sku);
              }

            }
            $data['configurableCombinations'][$data['sku']]['configurables'][$key]['values'] = $size_values;
          }
        }

        // Prepare data for first child.
        foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
          $child = SKU::loadFromSku($child_sku);
          if (!$child instanceof SKUInterface) {
            continue;
          }

          $options = NestedArray::mergeDeepArray([
            $options,
            $this->skuManager->getCombinationArray($combination),
          ], TRUE);
          $data['configurableCombinations'][$data['sku']]['combinations'] = $options;

          // Get the first child from attribute_sku.
          $sorted_variants = array_values(array_values($combinations['attribute_sku'])[0])[0];
          $data['configurableCombinations'][$data['sku']]['firstChild'] = reset($sorted_variants);
        }

        // Removing data that are not being used in new PDP.
        unset($data['swatch_data']);
        unset($data['cart_combinations']);
        unset($data['categorisations']);
        unset($data['delivery_options']);
        unset($data['linked']);
      }
    }

    if ($current_request->query->get('context') == 'cart') {
      // Adding extra data to the product resource.
      $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
      $data['extra_data'] = [];
      $image = alshaya_acm_get_product_display_image($sku, SkuImagesHelper::STYLE_CART_THUMBNAIL, 'cart');
      if (!empty($image)) {
        if ($image['#theme'] == 'image_style') {
          $data['extra_data']['cart_image'] = [
            'url' => ImageStyle::load($image['#style_name'])->buildUrl($image['#uri']),
            'title' => $image['#title'],
            'alt' => $image['#alt'],
          ];
        }
        elseif ($image['#theme'] == 'image') {
          $data['extra_data']['cart_image'] = [
            'url' => $image['#attributes']['src'],
            'title' => $image['#attributes']['title'],
            'alt' => $image['#attributes']['alt'],
          ];
        }
      }

      if ($this->skuManager->isSkuFreeGift($sku)) {
        $configurable_values = alshaya_acm_product_get_sku_configurable_values($sku);
        if (!empty($configurable_values)) {
          $formatted_configurable_values = [];
          foreach ($configurable_values as $attribute_id => $configurable_value) {
            $formatted_configurable_values[] = [
              'label' => $configurable_value['label'],
              'value' => $configurable_value['value'],
              'attribute_id' => $attribute_id,
            ];
          }
          $data['configurable_values'] = $formatted_configurable_values;
        }
      }

      // Removing media if context set as we don't require and to
      // make response light.
      unset($data['media']);
    }

    // Allow other modules to alter light product data.
    $type = 'full';
    if ($this->skuManager->isSkuFreeGift($sku)) {
      $this->moduleHandler->alter('alshaya_acm_product_gift_product_data', $sku, $data, $type);
    }
    else {
      $this->moduleHandler->alter('alshaya_acm_product_light_product_data', $sku, $data, $type);
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
              $this->languageManager->getCurrentLanguage()->getId())
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

      if (isset($promotion['type']) && $promotion['type'] === 'free_gift') {
        continue;
      }
      // Load promotion object.
      $promo_node = $this->nodeStorage->load($nid);
      $promotions[] = [
        'text' => $promotion['text'],
        'promo_web_url' => str_replace('/' . $this->languageManager->getCurrentLanguage()->getId() . '/',
          '',
          Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString(TRUE)->getGeneratedUrl()),
        'promo_node' => (int) $promo_node->get('field_acq_promotion_rule_id')->getString(),
      ];
    }
    return $promotions;
  }

  /**
   * Returns the configurable options minus the disabled options.
   *
   * This function removes the configurable options which are disabled and
   * returns the remaining.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   The sku object.
   * @param array $configurables
   *   The configurables array.
   *
   * @return array
   *   The configurables array.
   *
   * @see \Drupal\alshaya_acm_product\SkuManager::disableUnavailableOptions()
   */
  public function disableUnavailableOptions(SKUInterface $sku, array $configurables) {
    if (!empty($configurables)) {
      $combinations = $this->skuManager->getConfigurableCombinations($sku);
      // Remove all options which are not available at all.
      foreach ($configurables as $index => $code) {
        foreach ($configurables[$index]['values'] as $key => $value) {
          if (isset($combinations['attribute_sku'][$index][$value['value_id']])) {
            continue;
          }
          unset($configurables[$index]['values'][$key]);
        }
      }
      return $configurables;
    }

    return [];
  }

  /**
   * Helper function to get alternative group values of the variant.
   *
   * @return array
   *   The alternative array.
   */
  public function getAlternativeValues($alternates, $child) {
    $group_data = [];
    // Get all alternate labels from child sku.
    foreach ($alternates as $alternate => $alternate_label) {
      $attribute_code = 'attr_' . $alternate;
      $group_data[$alternate_label] = $child->get($attribute_code)->getString();
    }
    return $group_data;
  }

}
