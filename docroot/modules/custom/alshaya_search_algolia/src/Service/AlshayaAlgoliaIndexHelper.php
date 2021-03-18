<?php

namespace Drupal\alshaya_search_algolia\Service;

use AlgoliaSearch\Client;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyAliases;
use Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\file\FileInterface;
use Drupal\alshaya_product_options\SwatchesHelper;
use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Core\Language\LanguageManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;

/**
 * Class Alshaya Algolia Index Helper.
 *
 * @package Drupal\alshaya_search_algolia\Service
 */
class AlshayaAlgoliaIndexHelper {

  use StringTranslationTrait;

  const FACET_SOURCE = 'search_api:views_page__search__page';

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
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The super category manager service.
   *
   * @var \Drupal\alshaya_super_category\AlshayaSuperCategoryManager
   */
  protected $superCategoryManager;

  /**
   * Product category tree manager.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * The pretty path helper service.
   *
   * @var \Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper
   */
  protected $alshayaPrettyPathHelper;

  /**
   * The facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetsManager;

  /**
   * Pretty Path aliases.
   *
   * @var \Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyAliases
   */
  protected $prettyAliases;

  /**
   * Product Info Helper.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  protected $productInfoHelper;

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
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager service.
   * @param Drupal\alshaya_super_category\AlshayaSuperCategoryManager $super_category_manager
   *   The super category manager service.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $productCategoryTree
   *   Product category tree manager.
   * @param \Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper $pretty_path_helper
   *   The pretty path helper service.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facets_manager
   *   The facet manager.
   * @param \Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyAliases $pretty_aliases
   *   Pretty Aliases.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info Helper.
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
    SwatchesHelper $swatches_helper,
    LanguageManager $language_manager,
    AlshayaSuperCategoryManager $super_category_manager,
    ProductCategoryTree $productCategoryTree,
    AlshayaFacetsPrettyPathsHelper $pretty_path_helper,
    DefaultFacetManager $facets_manager,
    AlshayaFacetsPrettyAliases $pretty_aliases,
    ProductInfoHelper $product_info_helper
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
    $this->languageManager = $language_manager;
    $this->superCategoryManager = $super_category_manager;
    $this->productCategoryTree = $productCategoryTree;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->alshayaPrettyPathHelper = $pretty_path_helper;
    $this->facetsManager = $facets_manager;
    $this->prettyAliases = $pretty_aliases;
    $this->productInfoHelper = $product_info_helper;
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

    // Get processed title for a node.
    $object['title'] = $this->productInfoHelper->getValue($sku, 'title', 'plp', $object['title']);

    // Description.
    $description = $this->skuManager->getDescription($sku, 'full');
    $object['body'] = $this->renderer->renderPlain($description);

    // Override the config language to the language of the node.
    $language = $this->languageManager->getLanguage($node->language()->getId());
    $original_language = $this->languageManager->getConfigOverrideLanguage();
    $this->languageManager->setConfigOverrideLanguage($language);

    $sku_price_block = $this->skuPriceHelper->getPriceBlockForSku($sku);
    $object['rendered_price'] = $this->renderer->renderPlain($sku_price_block);

    // Restore the language manager to it's original language.
    $this->languageManager->setConfigOverrideLanguage($original_language);

    $prices = $this->skuManager->getMinPrices($sku, $product_color);
    $object['original_price'] = (float) $prices['price'];
    $object['price'] = (float) $prices['price'];
    $object['final_price'] = (float) $prices['final_price'];
    // Used for highest discount.
    $object['special_price'] = (float) $prices['special_price'];

    // Use max of selling prices for price in configurable products.
    if (!empty($prices['children'])) {
      $selling_prices = array_filter(array_column($prices['children'], 'selling_price'));
      $object['price'] = max($selling_prices);
      $object['special_price'] = max(array_filter(array_column($prices['children'], 'special_price')));

      $selling_prices = array_unique([
        min($selling_prices),
        max($selling_prices),
      ]);
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
      $node = $this->nodeStorage->load($nid);
      $available_translation_langcode = $node->getTranslationLanguages();
      foreach ($available_translation_langcode as $langcode => $value) {
        $promotion['url_' . $langcode] = Url::fromRoute('entity.node.canonical', ['node' => $nid], ['language' => $this->languageManager->getLanguage($langcode)])->toString();
      }
      $promotion['id'] = $nid;
    });

    $object['promotions'] = array_values($promotions);

    foreach ($object['promotions'] ?? [] as $promotionRecord) {
      // Used for filtering.
      $object['promotion_nid'][] = $promotionRecord['id'];

      // Used for facets.
      if (in_array('web', $promotionRecord['context'])) {
        $object['field_acq_promotion_label']['web'][] = $promotionRecord['text'];
      }
      if (in_array('app', $promotionRecord['context'])) {
        $object['field_acq_promotion_label']['app'][] = $promotionRecord['text'];
      }
    }

    // Product Images.
    $object['media'] = $this->getMediaItems($sku, $product_color);

    // Product Swatches.
    $swatches = $this->getAlgoliaSwatchData($sku);
    if (isset($swatches['swatches'])) {
      $object['swatches'] = array_values($swatches['swatches']);
    }

    // Index color facets.
    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    $swatch_plp_attributes = $display_settings->get('swatches.plp');

    if (!empty($swatch_plp_attributes)) {
      $this->processSwatchColorFacets($object, $swatch_plp_attributes);
    }

    if ($product_collection = $sku->get('attr_product_collection')->getString()) {
      $object['attr_product_collection'] = $product_collection;
    }

    if ($attr_style = $sku->get('attr_style')->getString()) {
      $object['attr_style'] = $attr_style;
    }

    $attr_barcode = $sku->get('attr_aims_barcode')->getString();
    if ($attr_barcode) {
      $object['attr_aims_barcode'] = $attr_barcode;
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

    $category_hierarchy = $this->getCategoryHierarchy($node, $node->language()->getId());
    $object['field_category_name'] = $category_hierarchy['names'];
    $object['field_category_aliases'] = $category_hierarchy['aliases'];

    $category_hierarchy = $this->getCategoryHierarchy($node, $node->language()->getId(), TRUE);
    $object['lhn_category'] = $category_hierarchy['names'];

    $langcode = $node->language()->getId();
    $object['field_category'] = $this->getFieldCategoryHierarchy($node, $langcode);

    // Index the product super_category terms.
    $super_categories = $this->superCategoryManager->getSuperCategories($node);
    // Check if super category is enabled.
    if ($super_categories !== FALSE) {
      $super_category_list = [
        $this->t('All', [], ['langcode' => $node->language()->getId()])->__toString(),
      ];
      // Check if some super category is present.
      if (!empty($super_categories)) {
        $super_category_list = array_merge($super_category_list, $super_categories);
      }
      $object[AlshayaSuperCategoryManager::SEARCH_FACET_NAME] = $super_category_list;
    }

    $object['is_new'] = $sku->get('attr_is_new')->getString();
    // Used for new arrivals.
    $object['created'] = $sku->get('created')->getString();
    $this->updatePrettyPathAlias($object);
    unset($object['field_category_aliases']);
  }

  /**
   * Update pretty paths table with facet aliases for given object.
   *
   * @param array $object
   *   The object to be indexed to algolia.
   */
  protected function updatePrettyPathAlias(array $object) {
    $facets = $this->getSearchFacets();

    $field_map = [
      'field_acq_promotion_label' => 'promotions',
      'field_category' => 'field_category_aliases',
    ];

    $langcode = $object['search_api_language'];

    foreach ($facets as $key => $facet_alias) {
      $field_key = isset($field_map[$key]) ? $field_map[$key] : $key;

      if (empty($object[$field_key])) {
        continue;
      }

      // Prepare the field name to get the field alias.
      $field_name = strpos($key, 'attr') !== FALSE || isset($field_map[$key])
        ? $key
        : NULL;

      if (empty($field_name)) {
        continue;
      }

      if (is_array($object[$field_key])) {
        $pure_values = $object[$field_key];
        array_walk(
          $pure_values,
          function (&$item, $key) use ($facets, $field_name, $langcode, $facet_alias) {
            if (!empty($facets[$field_name])) {
              $facet_item_value = $item;
              if (is_array($facet_item_value)) {
                $facet_item_value = ($field_name == 'field_acq_promotion_label') ? trim($item['text']) : trim($item['value']);
              }

              if ($facet_alias == 'category') {
                $this->prettyAliases->addAlias($facet_alias, $facet_item_value, $key, $langcode);
              }
              else {
                $this->alshayaPrettyPathHelper->encodeFacetUrlComponents(
                  self::FACET_SOURCE,
                  $facet_alias,
                  $facet_item_value,
                  $langcode
                );
              }
            }
          });
      }
      else {
        $this->alshayaPrettyPathHelper->encodeFacetUrlComponents(
          self::FACET_SOURCE,
          $facet_alias,
          trim($object[$field_key]),
          $langcode
        );
      }
    }
  }

  /**
   * Get the mapping of the facets key and alias.
   *
   * @return array
   *   the array of facet key and alias.
   */
  protected function getSearchFacets() {
    static $static = [];

    if (!empty($static)) {
      return $static;
    }

    // Get all facets of the given source.
    $facets = $this->facetsManager->getFacetsByFacetSourceId(self::FACET_SOURCE);
    foreach ($facets ?? [] as $facet) {
      $static[$facet->getFieldIdentifier()] = $facet->getUrlAlias();
    }

    return $static;
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
   * @param bool $only_visible_items
   *   Display only visible items, true to generate array of items which are
   *   visible either in main menu or in lhn.
   *
   * @return array[]
   *   The array of hierarchy.
   */
  protected function getCategoryHierarchy(NodeInterface $node, $langcode, $only_visible_items = FALSE): array {
    $categories = $node->get('field_category')->referencedEntities();

    $list = $aliases = [];
    foreach ($categories as $category) {
      $category = $this->entityRepository->getTranslationFromContext($category, $langcode);

      // Skip the term which is disabled.
      if ($category->get('field_commerce_status')->getString() !== '1') {
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

        // Skip the terms marked as not to visible in menu.
        if ($only_visible_items && $term->get('field_category_include_menu')->getString() !== '1') {
          continue;
        }

        $temp_list[] = $term->label();
        $current_value = implode(' > ', $temp_list);
        $aliases[$term->id()] = $current_value;

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
    return [
      'names' => $flat_hierarchy,
      'aliases' => $aliases,
    ];
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

    $config = $this->configFactory->get('alshaya_search_algolia.settings');
    $show_terms_in_lhn = $config->get('show_terms_in_lhn');

    foreach ($categories as $category) {
      // Skip the term which is disabled or not included in menu.
      if ($category->get('field_commerce_status')->getString() !== '1' || $category->get('field_category_include_menu')->getString() !== '1') {
        continue;
      }

      $parents = $this->productCategoryTree->getAllParents($category);

      if ($show_terms_in_lhn == 'all') {
        $trim_parents = $parents;
      }
      elseif (in_array($category->id(), $sale_categories)) {
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

  /**
   * Helper function to get Swatch facets.
   *
   * @param array $object
   *   The array of object being indexed.
   * @param array $swatch_plp_attributes
   *   The swatch plp attributes.
   */
  public function processSwatchColorFacets(array &$object, array $swatch_plp_attributes) {
    foreach ($swatch_plp_attributes as $attr) {
      $attr = 'attr_' . $attr;
      if (!empty($object[$attr])) {
        foreach ($object[$attr] as $key => $value) {
          $swatch = $this->swatchesHelper->getSwatch(substr($attr, 5), !empty($value['label']) ? $value['label'] : $value, $object['search_api_language']);
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
                continue 2;
            }
            $object[$attr][$key] = $swatch_data;
          }
        }
      }
    }

  }

  /**
   * Helps to add custom facet to the index.
   *
   * @param string|array $attributes
   *   The name of the attribute.
   *
   * @throws \Exception
   *   If attribute is already present in the index, then exception is thrown.
   */
  public function addCustomFacetToIndex($attributes) {
    $attributes = is_array($attributes) ? $attributes : [$attributes];

    // @todo If an entity field is to be added, the function should be modified
    // such that $attr_name should have prefix "attr_" as that is the
    // general syntax that can be seen.
    $backend_config = $this->configFactory->get('search_api.server.algolia')->get('backend_config');
    $client_config = $this->configFactory->get('search_api.index.alshaya_algolia_index')->get('options');
    $client = new Client($backend_config['application_id'], $backend_config['api_key']);
    $index_name = $client_config['algolia_index_name'];

    foreach ($this->languageManager->getLanguages() as $language) {
      $updated = FALSE;
      $index = $client->initIndex($index_name . '_' . $language->getId());
      $settings = $index->getSettings();

      foreach ($attributes as $attribute_name) {
        if (in_array($attribute_name, $settings['attributesForFaceting'])) {
          $this->logger->error("The attribute $attribute_name is already added to the index.");
          continue;
        }

        $settings['attributesForFaceting'][] = $attribute_name;
        $updated = TRUE;
      }

      if ($updated) {
        $index->setSettings($settings);

        foreach ($settings['replicas'] as $replica) {
          $replica_index = $client->initIndex($replica);
          $replica_settings = $replica_index->getSettings();
          $replica_settings['attributesForFaceting'] = $settings['attributesForFaceting'];
          $replica_index->setSettings($replica_settings);
        }
      }
    }

    $this->logger->notice('Added attribute(s) for faceting: @attributes', [
      '@attributes' => implode(',', $attributes),
    ]);
  }

  /**
   * Helps to update/remove replicas index in algolia.
   *
   * @param array $sorts
   *   The list of the fields to be used in sort index.
   * @param int $req_attempts
   *   No of request attempts to algolia.
   */
  public function updateReplicaIndex(array $sorts, int $req_attempts = 0) {
    try {
      $backend_config = $this->configFactory->get('search_api.server.algolia')->get('backend_config');
      $client_config = $this->configFactory->get('search_api.index.alshaya_algolia_index')->get('options');
      $client = new Client($backend_config['application_id'], $backend_config['api_key']);
      $index_name = $client_config['algolia_index_name'];

      foreach ($this->languageManager->getLanguages() as $language) {
        $index = $client->initIndex($index_name . '_' . $language->getId());
        $name = $index_name . '_' . $language->getId();
        $settings = $index->getSettings();
        unset($settings['replicas']);
        $ranking = $settings['ranking'];

        foreach ($sorts as $sort) {
          $replica = $name . '_' . implode('_', $sort);
          $settings['replicas'][] = $replica;
        }

        $index->setSettings($settings, TRUE);

        foreach ($sorts as $sort) {
          $replica = $name . '_' . implode('_', $sort);
          $replica_index = $client->initIndex($replica);
          $replica_settings = $replica_index->getSettings();
          $replica_settings['ranking'] = [
            'desc(stock)',
            $sort['direction'] . '(' . $sort['field'] . ')',
          ] + $ranking;
          $replica_index->setSettings($replica_settings);
        }
      }
      sleep(3);
    }
    catch (\Exception $e) {
      $this->logger->warning('Error occurred while creating replica index: %message', [
        '%message' => $e->getMessage(),
      ]);

      if ($req_attempts < 3) {
        return $this->updateReplicaIndex($sorts, $req_attempts++);
      }
    }
  }

  /**
   * Helps to prepare fields to create replicas.
   *
   * @param array $sort_options
   *   The list of options.
   */
  public function prepareFieldsToSort(array $sort_options) {
    $sorts = [];
    foreach ($sort_options as $value) {
      if ($value !== 0) {
        if ($value != 'created') {
          $sorts[] = ['field' => $value, 'direction' => 'asc'];
        }
        $sorts[] = ['field' => $value, 'direction' => 'desc'];
      }
    }

    return $sorts;
  }

}
