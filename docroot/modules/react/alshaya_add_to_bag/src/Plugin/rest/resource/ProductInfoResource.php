<?php

namespace Drupal\alshaya_add_to_bag\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_color_split\AlshayaColorSplitManager;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\node\NodeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get product details for drawer.
 *
 * @RestResource(
 *   id = "product_info",
 *   label = @Translation("Product Info"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/product-info/{sku}"
 *   }
 * )
 */
class ProductInfoResource extends ResourceBase {

  /**
   * SKU Info Helper service.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * SKU Images Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * SKU Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Product Info Helper service.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  protected $productInfoHelper;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * DrawerInfo constructor.
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
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   SKU Info Helper service.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info Helper service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    SkuInfoHelper $sku_info_helper,
    SkuImagesManager $sku_images_manager,
    SkuManager $sku_manager,
    ProductInfoHelper $product_info_helper,
    ModuleHandlerInterface $module_handler,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->skuInfoHelper = $sku_info_helper;
    $this->skuImagesManager = $sku_images_manager;
    $this->skuManager = $sku_manager;
    $this->productInfoHelper = $product_info_helper;
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
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
      $container->get('logger.factory')->get('alshaya_add_to_bag'),
      $container->get('alshaya_acm_product.sku_info'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('acq_sku.product_info_helper'),
      $container->get('module_handler'),
      $container->get('language_manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing checkout settings.
   */
  public function get(string $sku) {
    $sku = base64_decode($sku);

    try {
      $sku = SKU::loadFromSku($sku);
      if (!($sku instanceof SKUInterface)) {
        throw new \Exception(_alshaya_spc_global_error_message(), 404);
      }

      $node = $this->skuManager->getDisplayNode($sku);
      if (!($node instanceof NodeInterface)) {
        throw new \Exception(_alshaya_spc_global_error_message(), 404);
      }

      $data = [];
      $langcode = $this->languageManager->getCurrentLanguage()->getId();

      // Set SKU type.
      $sku_type = $sku->bundle();

      if ($sku_type === 'configurable') {
        $product_tree = Configurable::deriveProductTree($sku);

        // Set the configurable combinations.
        $configurables = $product_tree['configurables'];
        $cart_combinations = $product_tree['combinations'];
        $isColorSplitEnabled = $this->moduleHandler->moduleExists('alshaya_color_split');

        foreach ($configurables ?? [] as $attribute_data) {
          $attribute_code = $attribute_data['code'];
          $configurable_attributes[$attribute_code] = [
            'is_pseudo_attribute' => $isColorSplitEnabled
            && ((int) $attribute_data['attribute_id'] === (int) AlshayaColorSplitManager::PSEUDO_COLOR_ATTRIBUTE_CODE)
            ? TRUE
            : FALSE,
            'is_swatch' => FALSE,
            'label' => $attribute_data['label'],
            'position' => $attribute_data['position'],
            'id' => $attribute_data['attribute_id'],
            'values' => [],
          ];
          foreach ($attribute_data['values'] as $value) {
            $configurable_attributes[$attribute_code]['values'][] = [
              'value' => $value['value_id'],
              'label' => $value['label'],
            ];
          }
        }

        // Set max_sale_qty_parent_enable FALSE by default.
        $data['max_sale_qty_parent_enable'] = FALSE;

        // If parent sku has a max sale quantity limit, enable the parent
        // max sale quantity check and pass the quantity for conditional checks.
        $parent_stock_info = $this->skuInfoHelper->stockInfo($sku);
        if (!empty($parent_stock_info['max_sale_qty'])) {
          $data['max_sale_qty_parent_enable'] = TRUE;
          $data['max_sale_qty_parent'] = $parent_stock_info['max_sale_qty'];
        }

        // Store a hierarchy of related attributes and values.
        $attribute_hierarchy = [];

        // Set some required data for each SKU.
        foreach ($cart_combinations['by_sku'] ?? [] as $child_sku => $combination) {
          $child_data = [];

          $child = SKU::loadFromSku($child_sku, $langcode);
          if ((!$child instanceof SKUInterface)) {
            continue;
          }
          elseif ($child->language()->getId() !== $sku->language()->getId()) {
            continue;
          }

          $child_data['sku'] = $child_sku;

          // Set parent SKU.
          $parent_sku = $this->skuManager->getParentSkuBySku($child);
          $child_data['parent_sku'] = !empty($parent_sku) ? $parent_sku->getSku() : NULL;

          // Set cart title.
          $child_data['cart_title'] = $this->skuInfoHelper->getCartTitle($child);

          // Set cart image.
          $child_data['cart_image'] = $this->skuInfoHelper->getCartImage($child);

          // Set media items.
          $child_data['media'] = $this->skuInfoHelper->getMedia($child, 'pdp');

          $prices = $this->skuManager->getMinPrices($child);

          // Set product labels data.
          $labels_data = $this->skuManager->getLabelsData($child, 'pdp');
          $child_data['product_labels'] = array_map(function ($label_data) {
            return [
              'image' => [
                'url' => $label_data['image']['url'],
                'title' => $label_data['image']['title'],
                'alt' => $label_data['image']['alt'],
              ],
              'position' => $label_data['position'],
            ];
          }, $labels_data);

          // Set prices.
          $child_data['original_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $prices['price']);
          $child_data['final_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $prices['final_price']);
          $child_data['discount_percentage'] = $this->skuManager->getDiscountedPercent((float) $prices['price'], (float) $prices['final_price']);

          // Index max sale quantity. Whether max sale quantity is enabled/
          // disabled will be passed in drupalSettings.
          $stock_info = $this->skuInfoHelper->stockInfo($child);
          $child_data['max_sale_qty'] = $stock_info['max_sale_qty'];

          $data['variants'][] = $child_data;

          // Update the mapping array.
          $attribute_hierarchy = NestedArray::mergeDeepArray([
            $attribute_hierarchy,
            $this->skuManager->getCombinationArray($combination),
          ], TRUE);
        }

        // Get the swatches data.
        $swatches = $this->skuImagesManager->getSwatchData($sku);
        if (isset($swatches['swatches'])) {
          // Mark the attributes which are swatches.
          if (in_array($swatches['attribute_code'], array_keys($configurable_attributes))
          && !$configurable_attributes[$swatches['attribute_code']]['is_swatch']) {
            $configurable_attributes[$swatches['attribute_code']]['is_swatch'] = TRUE;
            $configurable_attributes[$swatches['attribute_code']]['swatches'] = [];

            foreach ($swatches['swatches'] as $swatch) {
              $is_color_swatch = $isColorSplitEnabled && ($swatch['swatch_type'] == AlshayaColorSplitManager::PDP_SWATCH_RGB);
              $configurable_attributes[$swatches['attribute_code']]['swatches'][] = [
                'label' => $swatch['display_label'],
                'data' => $swatch['display_value'] ?? $swatch['image_url'] ?? NULL,
                'value' => $swatch['value'],
                'type' => $is_color_swatch ? 'color' : $swatch['swatch_type'],
              ];
            }
          }
        }

        // Set product title.
        $data['title'] = $this->productInfoHelper->getTitle($sku, 'modal');

        // Set the promotions for the product.
        $data['promotions'] = array_map(function ($promotion) {
          return [
            'label' => $promotion['text'],
            'url' => $promotion['promo_web_url'],
          ];
        }, $this->skuManager->getPromotions($sku));

        // Set the size guide data.
        $category = NULL;
        $field_category = $node->get('field_category')->first();
        if (!empty($field_category)) {
          $category = $field_category->entity;
        }
        $data['size_guide'] = _alshaya_acm_product_get_size_guide_info($category, $langcode);

        $configurable_attributes = $this->disableUnavailableOptions($sku, $configurable_attributes);
        $data['configurable_attributes'] = $configurable_attributes;

        // Set the combinations by attribute to help determine the selected
        // variant.
        $data['configurable_combinations'] = [];
        $data['configurable_combinations']['by_attribute'] = $cart_combinations['by_attribute'];
        $data['configurable_combinations']['by_sku'] = $cart_combinations['by_sku'];
        $data['configurable_combinations']['attribute_hierarchy_with_values'] = $attribute_hierarchy;
      }

      $response = new ResourceResponse($data);
      $cacheableMetadata = $response->getCacheableMetadata();

      $cacheContexts = $sku->getCacheContexts();
      if (!empty($cacheContexts)) {
        $cacheableMetadata->addCacheContexts($sku->getCacheContexts());
      }

      $cacheTags = $sku->getCacheTags();
      if (!empty($cacheTags)) {
        $cacheableMetadata->addCacheTags($sku->getCacheTags());
      }

      $response->addCacheableDependency($cacheableMetadata);
      return $response;
    }
    catch (\Exception $e) {
      $errorData = [
        'error' => TRUE,
        'error_message' => $e->getMessage(),
        'error_code' => $e->getCode(),
      ];

      $response = new ResourceResponse($errorData);
      return $response;
    }
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
  private function disableUnavailableOptions(SKUInterface $sku, array $configurables) {
    if (!empty($configurables)) {
      $combinations = $this->skuManager->getConfigurableCombinations($sku);
      // Remove all options which are not available at all.
      foreach ($configurables as $index => $code) {
        $option_unavailable_flag = FALSE;
        foreach ($configurables[$index]['values'] as $key => $value) {
          if (isset($combinations['attribute_sku'][$index][$value['value']])) {
            continue;
          }
          $option_unavailable_flag = TRUE;
          unset($configurables[$index]['values'][$key]);
        }
        // Reindex the array after unset.
        $configurables[$index]['values'] = $option_unavailable_flag
          ? array_values($configurables[$index]['values'])
          : $configurables[$index]['values'];
      }
      return $configurables;
    }

    return [];
  }

}
