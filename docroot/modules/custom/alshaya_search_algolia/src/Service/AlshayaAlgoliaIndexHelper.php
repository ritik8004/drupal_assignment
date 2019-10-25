<?php

namespace Drupal\alshaya_search_algolia\Service;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\NodeInterface;

/**
 * Class AlshayaAlgoliaIndexHelper.
 *
 * @package Drupal\alshaya_search_algolia\Service
 */
class AlshayaAlgoliaIndexHelper {

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
    EntityRepositoryInterface $entity_repository
  ) {
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->renderer = $renderer;
    $this->skuInfoHelper = $sku_info_helper;
    $this->logger = $logger_factory->get('alshaya_search_algolia');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->entityRepository = $entity_repository;
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
    $swatches = $this->skuImagesManager->getSwatchData($sku);
    if (isset($swatches['swatches'])) {
      $object['swatches'] = array_values($swatches['swatches']);
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
   *     "lvl0": "Movie",
   *     "lvl1": "Movie > Science Fiction",
   *     "lvl2": "Movie > Science Fiction > Time Travel"],
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
        // Break the loop if any level of term is disabled or the term is not
        // included in menu.
        if ($term->get('field_commerce_status')->getString() !== '1' || $term->get('field_category_include_menu')->getString() !== '1') {
          // Remove the parent hierarchy, If the hierarchy initiated for this
          // specific loop.
          if (empty($list[$parent_key]["lvl{$i}"])) {
            $previous = $i - 1;
            if (is_string($list[$parent_key]["lvl{$previous}"])) {
              unset($list[$parent_key]["lvl{$previous}"]);
              if (empty($list[$parent_key])) {
                unset($list[$parent_key]);
              }
            }
          }
          break;
        }

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

}
