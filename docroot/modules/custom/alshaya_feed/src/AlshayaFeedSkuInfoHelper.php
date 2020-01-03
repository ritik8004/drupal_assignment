<?php

namespace Drupal\alshaya_feed;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\alshaya_acm\Service\AlshayaAcmApiWrapper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;

/**
 * Class SkuInfoHelper.
 *
 * @package Drupal\alshaya_feed
 */
class AlshayaFeedSkuInfoHelper {

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
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * SkuInfoHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    SkuManager $sku_manager,
    SkuImagesManager $sku_images_manager,
    ModuleHandlerInterface $module_handler,
    SkuInfoHelper $sku_info_helper,
    SKUFieldsManager $sku_fields_manager,
    RendererInterface $renderer
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->moduleHandler = $module_handler;
    $this->skuInfoHelper = $sku_info_helper;
    $this->skuFieldsManager = $sku_fields_manager;
    $this->renderer = $renderer;
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
  public function prepareFeedData(int $nid): array {
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

    $product = [];
    foreach ($this->languageManager->getLanguages() as $lang => $language) {
      $node = $this->skuInfoHelper->getEntityTranslation($node, $lang);
      if ($node->language()->getId() !== $lang) {
        continue;
      }
      $sku = $this->skuInfoHelper->getEntityTranslation($sku, $lang);

      // Get the prices.
      $color = ($this->skuManager->isListingModeNonAggregated()) ? $node->get('field_product_color')->getString() : '';
      $prices = $this->skuManager->getMinPrices($sku, $color);

      $short_desc = $this->skuManager->getShortDescription($sku, 'full');
      $description = $this->skuManager->getDescription($sku, 'full');

      $product[$lang] = [
        'sku' => $sku->getSku(),
        'name' => $node->label(),
        'product_type' => $sku->bundle(),
        'status' => (bool) $node->isPublished(),
        'url' => $this->skuInfoHelper->getEntityUrl($node),
        'short_description' => !empty($short_desc['value']) ? $this->renderer->renderPlain($short_desc['value']) : '',
        'description' => !empty($description) ? $this->renderer->renderPlain($description) : '',
        'original_price' => $this->skuInfoHelper->formatPriceDisplay((float) $prices['price']),
        'final_price' => $this->skuInfoHelper->formatPriceDisplay((float) $prices['final_price']),
        'categoryCollection' => $this->skuInfoHelper->getProductCategories($node, $lang),
        'attributes' => $this->skuInfoHelper->getAttributes($sku, ['description', 'short_description']),
      ];

      if ($sku->bundle() == 'simple') {
        $stockInfo = $this->skuInfoHelper->stockInfo($sku);
        $product[$lang]['stock'] = [
          'status' => $stockInfo['in_stock'],
          'qty' => $stockInfo['stock'],
        ];
        $product[$lang]['images'] = $this->getGalleryMedia($sku);
      }
      elseif ($sku->bundle() === 'configurable') {
        $combinations = $this->skuManager->getConfigurableCombinations($sku);
        $swatches = $this->skuImagesManager->getSwatchData($sku);
        foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
          $child = SKU::loadFromSku($child_sku, $lang);
          if (!$child instanceof SKUInterface) {
            continue;
          }
          $stockInfo = $this->skuInfoHelper->stockInfo($child);

          $variant = [
            'sku' => $child->getSku(),
            'configurable_attributes' => $this->getConfigurableValues($child, $combination),
            'swatch_image' => $this->getSwatchImages($child, $combination, $swatches),
            'images' => $this->getGalleryMedia($child),
            'stock' => [
              'status' => $stockInfo['in_stock'],
              'qty' => $stockInfo['stock'],
            ],
          ];
          $product[$lang]['variants'][] = $variant;
        }
      }

      $product[$lang]['linked_skus'] = [];
      foreach (AcqSkuLinkedSku::LINKED_SKU_TYPES as $linked_type) {
        $linked_skus = $this->skuInfoHelper->getLinkedSkus($sku, $linked_type);
        $product[$lang]['linked_skus'][$linked_type] = array_keys($linked_skus);
      }
    }

    return $product;
  }

  /**
   * Wrapper function get swatch images.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param array $combination
   *   Array of combination attributes.
   * @param array $swatches
   *   Array of swatches.
   *
   * @return array
   *   An array swatch image with label.
   */
  protected function getSwatchImages(SKUInterface $sku, array $combination, array $swatches): array {
    $swatch_image = [];

    if (!empty($swatches['swatches']) && !empty($swatches['attribute_code']) && !empty($combination[$swatches['attribute_code']])) {
      $swatch_image = $swatches['swatches'][$combination[$swatches['attribute_code']]] ?? [];
      if (empty($swatch_image['display_value']) && !empty($swatch_image['image_url'])) {
        $swatch_image['display_value'] = $swatch_image['image_url'];
      }
    }

    return $swatch_image;
  }

  /**
   * Wrapper function get gallery images.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Configurable Values.
   */
  protected function getGalleryMedia(SKUInterface $sku): array {
    $media_items = $this->skuImagesManager->getProductMedia($sku, 'pdp');
    if (empty($media_items) || empty($media_items['media_items']) || !is_array($media_items['media_items']['images'])) {
      return [];
    }

    $images = array_map(function ($image) {
      if (!empty($image['drupal_uri'])) {
        return [
          'label' => $image['label'],
          'url' => file_create_url($image['drupal_uri']),
        ];
      }
    }, $media_items['media_items']['images']);

    return array_filter($images);
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
  public function getConfigurableValues(SKUInterface $sku, array $attributes = []): array {
    if ($sku->bundle() !== 'simple') {
      return [];
    }

    $configurableFieldValues = [];
    $remove_not_required_option = $this->skuManager->isNotRequiredOptionsToBeRemoved();
    foreach ($attributes as $code => $id) {
      $fieldKey = 'attr_' . $code;

      if ($sku->hasField($fieldKey) && $value = $sku->get($fieldKey)->getString()) {
        if ($remove_not_required_option && $this->skuManager->isAttributeOptionToExclude($value)) {
          continue;
        }
        $configurableFieldValues[$code] = $value;
      }
      else {
        $sku_attributes = $this->getSkuAttributes($sku);
        if (empty($sku_attributes)
            || !isset($sku_attributes[$code][$id])
            || ($remove_not_required_option && $this->skuManager->isAttributeOptionToExclude($sku_attributes[$code][$id]))
        ) {
          continue;
        }
        $configurableFieldValues[$code] = $sku_attributes[$code][$id];
      }
    }

    return $configurableFieldValues;
  }

  /**
   * Get the attributes of given sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Return array of attributess.
   */
  public function getSkuAttributes(SKUInterface $sku): array {
    $static = &drupal_static(__METHOD__, []);

    $parent = $this->skuManager->getParentSkuBySku($sku, $sku->language()->getId());

    $cid = implode(':', [
      $parent->getSku(),
      $parent->language()->getId(),
    ]);

    // Do not process the same thing again and again.
    if (isset($static[$cid])) {
      return $static[$cid];
    }

    $configurables = unserialize($parent->get('field_configurable_attributes')->getString());
    if (empty($configurables) || !is_array($configurables)) {
      return [];
    }
    $configurations = [];
    foreach ($configurables as $configuration) {
      $configurations[$configuration['code']] = array_combine(array_column($configuration['values'], 'value_id'), array_column($configuration['values'], 'label'));
    }

    $static[$cid] = $configurations;
    return $configurations;
  }

}
