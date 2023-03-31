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
use Drupal\alshaya_product_options\ProductOptionsHelper;
use Drupal\acq_sku\CartFormHelper;
use Drupal\Core\Config\ConfigFactoryInterface;

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
   * Product Options Helper.
   *
   * @var \Drupal\alshaya_product_options\ProductOptionsHelper
   */
  protected $optionsHelper;

  /**
   * Cart Form Helper service.
   *
   * @var \Drupal\acq_sku\CartFormHelper
   */
  protected $cartFormHelper;

  /**
   * Checkout settings config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

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
   * @param \Drupal\alshaya_product_options\ProductOptionsHelper $options_helper
   *   Product Options Helper.
   * @param \Drupal\acq_sku\CartFormHelper $cartform_helper
   *   Cart Form Helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
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
    LanguageManagerInterface $language_manager,
    ProductOptionsHelper $options_helper,
    CartFormHelper $cartform_helper,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->skuInfoHelper = $sku_info_helper;
    $this->skuImagesManager = $sku_images_manager;
    $this->skuManager = $sku_manager;
    $this->productInfoHelper = $product_info_helper;
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
    $this->optionsHelper = $options_helper;
    $this->cartFormHelper = $cartform_helper;
    $this->config = $config_factory->get('alshaya_acm_product.display_settings');
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
      $container->get('language_manager'),
      $container->get('alshaya_product_options.helper'),
      $container->get('acq_sku.cart_form_helper'),
      $container->get('config.factory'),
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing checkout settings.
   */
  public function get(string $sku) {
    $configurable_attributes = [];
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

        // Set catalog restructuring enabled or not.
        $data['catalogRestructured'] = $isColorSplitEnabled;

        foreach ($configurables ?? [] as $attribute_data) {
          $attribute_code = $attribute_data['code'];

          // Check for the grouped attributes (alternate options).
          $alternates = $this->optionsHelper->getSizeGroup($attribute_code);

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
            'is_group' => ($alternates ? TRUE : FALSE),
            'alternates' => $alternates,
          ];

          $sorted_values = $attribute_data['values'];
          if ($this->cartFormHelper->isAttributeSortable($attribute_code)) {
            $sorted_values = Configurable::sortConfigOptions($attribute_data['values'], $attribute_code);
          }

          foreach ($sorted_values as $value) {
            $value_id = $value['value_id'];

            // Prepare labels for the grouped attributes (alternate options).
            if ($alternates) {
              foreach ($cart_combinations['attribute_sku'][$attribute_code][$value_id] ?? [] as $child_sku_code) {
                $child_sku = SKU::loadFromSku($child_sku_code, $sku->language()->getId());

                if (!($child_sku instanceof SKU)) {
                  continue;
                }

                $group_labels = $this->getAlternativeValues($alternates, $child_sku);
              }
            }

            $configurable_attributes[$attribute_code]['values'][] = [
              'value' => $value_id,
              'label' => $group_labels ?? $value['label'],
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
          $child_data['product_labels'] = array_map(fn($label_data) => [
            'image' => [
              'url' => $label_data['image']['url'],
              'title' => $label_data['image']['title'],
              'alt' => $label_data['image']['alt'],
            ],
            'position' => $label_data['position'],
          ], $labels_data);

          // Set prices.
          $child_data['original_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $prices['price']);
          $child_data['final_price'] = $this->skuInfoHelper->formatPriceDisplay((float) $prices['final_price']);
          // Fixed price is used by Cross Border sites.
          $child_data['fixed_price'] = $prices['fixed_price'] ?? '';
          $child_data['discount_percentage'] = $this->skuManager->getDiscountedPercent((float) $prices['price'], (float) $prices['final_price']);

          // Index max sale quantity. Whether max sale quantity is enabled/
          // disabled will be passed in drupalSettings.
          $stock_info = $this->skuInfoHelper->stockInfo($child);
          $child_data['max_sale_qty'] = $stock_info['max_sale_qty'];

          // Pass stock information to restrict the quantity options.
          $child_data['stock']['qty'] = $stock_info['stock'];
          $child_data['stock']['status'] = $stock_info['in_stock'];

          // Set the promotions for the variants.
          $child_data['promotions'] = array_map(fn($promotion) => [
            'label' => $promotion['text'],
            'url' => $promotion['promo_web_url'],
          ], $this->skuManager->getPromotions($child));

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
                'child_sku_code' => $swatch['child_sku_code'],
              ];
            }
          }
          foreach ($configurable_attributes[$swatches['attribute_code']]['swatches'] as $key => $value) {
            if (is_null($value['data'])) {
              $multiple_attributes_for_color = $this->config->get('color_attribute_config');
              // If site uses multiple attributes for color.
              if ($multiple_attributes_for_color && $multiple_attributes_for_color['support_multiple_attributes']) {
                $color_code_attribute = $multiple_attributes_for_color['configurable_color_code_attribute'];
                $child_node = SKU::loadFromSku($value['child_sku_code'], $langcode);
                $configurable_attributes[$swatches['attribute_code']]['swatches'][$key]['data'] = $color_code_attribute ? $child_node->get($color_code_attribute)->getString() : NULL;
              }
            }
          }
        }
        // Set product title.
        $data['title'] = $this->productInfoHelper->getTitle($sku, 'modal');

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

        // Get the first child from attribute_sku which has values sorted
        // same as add to cart form.
        if (array_key_exists('attribute_sku', $product_tree['combinations'])) {
          $sorted_variants = array_values(array_values($product_tree['combinations']['attribute_sku'])[0])[0];
          $data['configurable_combinations']['firstChild'] = reset($sorted_variants);
        }

        // Get the first child from variants of selected parent.
        foreach (Configurable::getChildSkus($product_tree['parent']) as $child_sku) {
          if (isset($product_tree['products'][$child_sku])) {
            $data['configurable_combinations']['firstChild'] = $child_sku;
            break;
          }
        }
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

  /**
   * Helper function to get alternative group values of the variant.
   *
   * @return array
   *   The alternative array.
   */
  private function getAlternativeValues($alternates, $child) {
    $group_data = [];
    // Get all alternate labels from child sku.
    foreach ($alternates as $alternate => $alternate_label) {
      $attribute_code = 'attr_' . $alternate;
      $group_data[$alternate_label] = $child->get($attribute_code)->getString();
    }
    return $group_data;
  }

}
