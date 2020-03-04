<?php

namespace Drupal\alshaya_search_algolia\Service;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\file\FileInterface;
use Drupal\alshaya_product_options\SwatchesHelper;

/**
 * Class AlshayaAlgoliaIndexHelper.
 *
 * @package Drupal\alshaya_search_algolia\Service
 */
class AlshayaAlgoliaIndexHelper {

  use StringTranslationTrait;

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
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Entity Repository object.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * The SKU Price Helper service.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuPriceHelper
   */
  protected $skuPriceHelper;

  /**
   * The product category manager service.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryManager
   */
  protected $productCategoryManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The Swatches Helper service.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  protected $swatchesHelper;

  /**
   * SkuInfoHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   The sku info helper service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository object.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   * @param \Drupal\alshaya_acm_product\Service\SkuPriceHelper $sku_price_helper
   *   The SKU price helper service.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryManager $product_category_manager
   *   The product category manager service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatches_helper
   *   The Swatches helper service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    SkuManager $sku_manager,
    SkuImagesManager $sku_images_manager,
    RendererInterface $renderer,
    SkuInfoHelper $sku_info_helper,
    LoggerChannelFactoryInterface $logger_factory,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository,
    TimeInterface $date_time,
    SkuPriceHelper $sku_price_helper,
    ProductCategoryManager $product_category_manager,
    ConfigFactory $config_factory,
    SwatchesHelper $swatches_helper
  ) {
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->renderer = $renderer;
    $this->skuInfoHelper = $sku_info_helper;
    $this->logger = $logger_factory->get('alshaya_search_algolia');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->entityRepository = $entity_repository;
    $this->dateTime = $date_time;
    $this->skuPriceHelper = $sku_price_helper;
    $this->productCategoryManager = $product_category_manager;
    $this->configFactory = $config_factory;
    $this->swatchesHelper = $swatches_helper;
  }

  /**
   * Helper function to process index item.
   *
   * @param array $object
   *   The array of object being indexed.
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function processIndexItem(array &$object, NodeInterface $node) {
    if (empty($object['sku'])) {
      throw new \Exception('SKU not available');
    }

    $product_color = '';
    if ($this->skuManager->isListingModeNonAggregated()) {
      $product_color = $node->get('field_product_color')->getString();
    }

    $sku = SKU::loadFromSku($object['sku'], $object['search_api_language']);

    if (!($sku instanceof SKUInterface)) {
      throw new \Exception('Not able to load sku from node.');
    }
    elseif ($sku->language()->getId() != $node->language()->getId()) {
      throw new \Exception('SKU not available for language of Node');
    }

    // Description.
    $description = $this->skuManager->getDescription($sku, 'full');
    $object['body'] = $this->renderer->renderPlain($description);

    $object['field_category_name'] = $this->getCategoryHierarchy($node, $node->language()->getId());
    $object['rendered_price'] = $this->renderer->renderPlain($this->skuPriceHelper->getPriceBlockForSku($sku));
    $prices = $this->skuManager->getMinPrices($sku, $product_color);
    $object['original_price'] = (float) $prices['price'];
    $object['price'] = (float) $prices['price'];
    $object['final_price'] = (float) $prices['final_price'];

    // Use max of selling prices for price in configurable products.
    if (!empty($prices['children'])) {
      $selling_prices = array_filter(array_column($prices['children'], 'selling_price'));
      $object['price'] = max($selling_prices);

      $selling_prices = array_unique([min($selling_prices), max($selling_prices)]);
      $object['attr_selling_price'] = $selling_prices;

      if ($this->skuManager->isPriceModeFromTo()) {
        $object['final_price'] = min($selling_prices);
      }
    }

    if ($sku->bundle() == 'configurable') {
      $configured_skus = $sku->get('field_configured_skus')->getValue();
      $object['field_configured_skus'] = array_map(function ($item) {
        return $item['value'];
      }, $configured_skus);
    }

    $object['attr_product_brand'] = $sku->get('attr_product_brand')->getString();

    // Set color / size and other configurable attributes data.
    try {
      $attributes = $this->skuManager->getConfigurableAttributesData($sku, $product_color);
      foreach ($attributes as $key => $values) {
        $object['attr_' . $key] = array_values($values);
      }
    }
    catch (\Throwable $e) {
      throw new \Exception($e->getMessage());
    }

    // Promotions.
    $promotions = $this->skuManager->getPromotionsForSearchViewFromSkuId($sku);
    array_walk($promotions, function (&$promotion, $nid) {
      $promotion['url'] = Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString();
    });

    // Removed 'field_acq_promotion_label' in favour of 'promotions'.
    $object['promotions'] = array_values($promotions);

    // Product Images.
    $object['media'] = $this->getMediaItems($sku, $product_color);

    // Product Swatches.
    $swatches = $this->getAlgoliaSwatchData($sku);
    if (isset($swatches['swatches'])) {
      $object['swatches'] = array_values($swatches['swatches']);
    }

    if (!empty($object['attr_color_family'])) {
      foreach ($object['attr_color_family'] as $key => $value) {
        $swatch = $this->swatchesHelper->getSwatch('color_family', $value, $object['search_api_language']);
        $swatch_data = [
          'value' => $swatch['name'],
          'label' => $swatch['name'],
        ];

        if ($swatch) {
          switch ($swatch['type']) {
            case SwatchesHelper::SWATCH_TYPE_TEXTUAL:
              $swatch_data['swatch_text'] = $swatch['swatch'];
              $swatch_data['label'] .= ', swatch_text:' . $swatch['swatch'];
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_COLOR:
              $swatch_data['swatch_color'] = $swatch['swatch'];
              $swatch_data['label'] .= ', swatch_color:' . $swatch['swatch'];
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_IMAGE:
              $swatch_data['swatch_image'] = $swatch['swatch'];
              $swatch_data['label'] .= ', swatch_image:' . $swatch['swatch'];
              break;

            default:
              continue;
          }
          $object['attr_color_family'][$key] = $swatch_data;
        }
      }
    }

    if ($product_collection = $sku->get('attr_product_collection')->getString()) {
      $object['attr_product_collection'] = $product_collection;
    }

    if ($attr_style = $sku->get('attr_style')->getString()) {
      $object['attr_style'] = $attr_style;
    }

    $object['url'] = $this->skuInfoHelper->getEntityUrl($node, FALSE);
    // Convert to array to always send key to index event with empty array.
    $object['product_labels'] = (array) $this->skuManager->getLabelsData($sku, 'plp');

    // Update stock info for product.
    $object['stock_quantity'] = $this->skuInfoHelper->calculateStock($sku);
    $object['stock'] = $this->skuManager->getStockStatusForIndex($sku);
    if ($object['stock'] === 0) {
      $this->removeAttributesFromIndex($object);
    }
    $object['changed'] = $this->dateTime->getRequestTime();
    $object['field_category'] = $this->getFieldCategoryHierarchy($node, $node->language()->getId());
  }

  /**
   * Prepare images with image style link to index.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   * @param string|null $product_color
   *   Color value.
   *
   * @return array
   *   Return array of images with url and image_type.
   *
   * @throws \Exception
   */
  public function getMediaItems(SKU $sku, $product_color = NULL): array {
    $sku_for_gallery = $this->skuImagesManager->getSkuForGalleryWithColor($sku, $product_color) ?? $sku;
    // @see \Drupal\alshaya_acm_product\SkuImagesManager::getGallery.
    $media = $this->skuImagesManager->getProductMedia($sku_for_gallery, 'search', FALSE);
    $images = [];

    foreach ($media['media_items']['images'] ?? [] as $media_item) {
      $images[] = [
        'url' => ImageStyle::load('product_listing')->buildUrl($media_item['drupal_uri']),
        'image_type' => $media_item['sortAssetType'] ?? 'image',
      ];
    }
    return $images;
  }

  /**
   * Wrapper function to update stock data for index item.
   *
   * @param array $item
   *   Index item.
   *
   * @see SkuManager::updateStockForIndex()
   */
  protected function removeAttributesFromIndex(array &$item) {
    // If product is not in stock, remove all attributes data.
    foreach ($item as $field_key => $field_val) {
      // Only unset/remove of attribute fields or this will remove the
      // SKU from the indexing on default listing (without any filter).
      if (strpos($field_key, 'attr_') !== FALSE) {
        unset($item[$field_key]);
      }
    }
  }

  /**
   * Create term hierarchy to index for Algolia.
   *
   * Prepares the array structure as shown below.
   * @code
   * [
   *   [
   *     "lvl0": "Books",
   *     "lvl1": ["Books > Science Fiction", "Books > Literature & Fiction"],
   *     "lvl2": [
   *       "Books > Science Fiction > Time Travel",
   *       "Books > Literature & Fiction > Modernism "
   *     ],
   *   ],
   *   [
   *     "lvl0": "Movies",
   *     "lvl1": "Movies > Science Fiction",
   *     "lvl2": "Movies > Science Fiction > Time Travel"],
   *   ],
   * ]
   * @endcode
   * OR
   * @code
   * {
   *   "lvl0": [
   *     "Books",
   *     "Movies"
   *   ],
   *   "lvl1": [
   *     "Books > Science Fiction",
   *     "Books > Literature & Fiction",
   *     "Movies > Science Fiction"
   *   ],
   *   "lvl2": [
   *     "Books > Science Fiction > Time Travel",
   *     "Books > Literature & Fiction > Modernism ",
   *     "Movies > Science Fiction > Time Travel"
   *   ]
   * }
   * @endcode
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object for which we need to prepare hierarchy.
   * @param string $langcode
   *   The language code to use to load terms.
   *
   * @return array
   *   The array of hierarchy.
   */
  protected function getCategoryHierarchy(NodeInterface $node, $langcode): array {
    $categories = $node->get('field_category')->referencedEntities();

    $list = [];
    foreach ($categories as $category) {
      // Skip the term which is disabled.
      if ($category->get('field_commerce_status')->getString() !== '1' || $category->get('field_category_include_menu')->getString() !== '1') {
        continue;
      }
      $parents = array_reverse($this->termStorage->loadAllParents($category->id()));
      $temp_list = [];
      $i = 0;
      // Top parent term id.
      $parent_key = NULL;
      foreach ($parents as $term) {
        if (empty($parent_key)) {
          $parent_key = $term->id();
        }

        $term = $this->entityRepository->getTranslationFromContext($term, $langcode);
        $temp_list[] = $term->label();
        $current_value = implode(' > ', $temp_list);

        if (empty($list[$parent_key]["lvl{$i}"])) {
          $list[$parent_key]["lvl{$i}"] = $current_value;
        }
        elseif (is_string($list[$parent_key]["lvl{$i}"]) && $list[$parent_key]["lvl{$i}"] !== $current_value) {
          $list[$parent_key]["lvl{$i}"] = array_merge([$list[$parent_key]["lvl{$i}"]], [$current_value]);
        }
        elseif (is_array($list[$parent_key]["lvl{$i}"])) {
          if (!in_array($current_value, $list[$parent_key]["lvl{$i}"])) {
            $list[$parent_key]["lvl{$i}"][] = $current_value;
          }
        }
        $i++;
      }
    }

    $flat_hierarchy = [];
    foreach (array_values($list) as $nesting) {
      foreach ($nesting as $level => $level_items) {
        if (empty($flat_hierarchy[$level])) {
          $flat_hierarchy[$level] = (array) $level_items;
        }
        else {
          $flat_hierarchy[$level] = array_merge($flat_hierarchy[$level], (array) $level_items);
        }
      }
    }
    return $flat_hierarchy;
  }

  /**
   * Create field_category term hierarchy to index for Algolia.
   *
   * Prepares the array structure as shown below.
   * @code
   * [
   *   [
   *     "lvl0": "Books",
   *   ],
   *   [
   *     "lvl0": "Movie",
   *     "lvl1": "Movie > Science Fiction",
   *   ],
   * ]
   * @endcode
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object for which we need to prepare hierarchy.
   * @param string $langcode
   *   The language code to use to load terms.
   *
   * @return array
   *   The array of hierarchy.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getFieldCategoryHierarchy(NodeInterface $node, $langcode): array {
    $categories = $node->get('field_category')->referencedEntities();
    $list = [];
    $list['all']['lvl0'] = $this->t('All', [], ['langcode' => $langcode]);
    // Get sales categories to index L2 for sales terms.
    $old_categorization_rule = $this->productCategoryManager->isOldCategorizationRuleEnabled();
    // If old categorization rule is not enabled
    // call getCategorizationIds() with ['sale'].
    if ($old_categorization_rule) {
      $sale_categories = $this->productCategoryManager->getCategorizationIds() ?? [];
    }
    else {
      $sale_categories = $this->productCategoryManager->getCategorizationIds()['sale'] ?? [];
    }
    foreach ($categories as $category) {
      // Skip the term which is disabled.
      if ($category->get('field_commerce_status')->getString() !== '1' || $category->get('field_category_include_menu')->getString() !== '1') {
        continue;
      }
      $parents = array_reverse($this->termStorage->loadAllParents($category->id()));
      if (in_array($category->id(), $sale_categories)) {
        // Passing the first two parents(l1&l2).
        $trim_parents = array_chunk($parents, 2)[0];
      }
      else {
        // Passing only l1.
        $trim_parents = array_chunk($parents, 1)[0];
      }
      $temp_list = [];
      $i = 0;
      // Top parent term id.
      $parent_key = NULL;
      foreach ($trim_parents as $term) {
        if (empty($parent_key)) {
          $parent_key = $term->id();
        }
        $term = $this->entityRepository->getTranslationFromContext($term, $langcode);
        $temp_list[] = $term->label();
        $current_value = implode(' > ', $temp_list);
        if (empty($list[$parent_key]["lvl{$i}"])) {
          $list[$parent_key]["lvl{$i}"] = $current_value;
        }
        elseif (is_string($list[$parent_key]["lvl{$i}"]) && $list[$parent_key]["lvl{$i}"] !== $current_value) {
          $list[$parent_key]["lvl{$i}"] = array_merge([$list[$parent_key]["lvl{$i}"]], [$current_value]);
        }
        elseif (is_array($list[$parent_key]["lvl{$i}"])) {
          if (!in_array($current_value, $list[$parent_key]["lvl{$i}"])) {
            $list[$parent_key]["lvl{$i}"][] = $current_value;
          }
        }
        $i++;
      }
    }
    return array_values($list);
  }

  /**
   * Get Swatches Data for particular configurable sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Swatches data.
   */
  public function getAlgoliaSwatchData(SKUInterface $sku): array {
    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    $swatches = $this->skuImagesManager->getSwatchData($sku);

    if (empty($swatches) || empty($swatches['swatches'])) {
      return [];
    }

    $index_product_image_url = TRUE;
    if (!$display_settings->get('color_swatches_show_product_image')) {
      $index_product_image_url = FALSE;
    }

    foreach ($swatches['swatches'] as $key => $swatch) {
      if ($index_product_image_url) {
        $child = SKU::loadFromSku($swatch['child_sku_code']);
        $swatch_product_image = $child->getThumbnail();
        // If we have image for the product.
        if (!empty($swatch_product_image) && $swatch_product_image['file'] instanceof FileInterface) {
          $url = file_create_url($swatch_product_image['file']->getFileUri());
          $swatch['product_image_url'] = $url;
        }
      }

      $swatches['swatches'][$key] = $swatch;
    }

    return $swatches;
  }

}
