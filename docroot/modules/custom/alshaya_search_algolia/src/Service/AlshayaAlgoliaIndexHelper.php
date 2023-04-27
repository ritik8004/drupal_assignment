<?php

namespace Drupal\alshaya_search_algolia\Service;

use Algolia\AlgoliaSearch\SearchClient;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\alshaya_acm_product\SkuImagesHelper;
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
use Drupal\node\NodeInterface;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\alshaya_product_options\SwatchesHelper;
use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Core\Language\LanguageManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Site\Settings;

/**
 * Class Alshaya Algolia Index Helper.
 *
 * @package Drupal\alshaya_search_algolia\Service
 */
class AlshayaAlgoliaIndexHelper {

  use StringTranslationTrait;

  public const FACET_SOURCE = 'search_api:views_page__search__page';

  /**
   * Index name for Search Page.
   */
  public const SEARCH_INDEX = 'alshaya_algolia_index';

  /**
   * Index name for product list on PLP, promo, brand listing.
   */
  public const PRODUCT_LIST_INDEX = 'alshaya_algolia_product_list_index';

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
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Sku images helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $skuImagesHelper;

  /**
   * AlshayaAlgoliaIndexHelper constructor.
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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $sku_images_helper
   *   Sku images helper.
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
    ProductInfoHelper $product_info_helper,
    ModuleHandlerInterface $module_handler,
    SkuImagesHelper $sku_images_helper
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
    $this->moduleHandler = $module_handler;
    $this->skuImagesHelper = $sku_images_helper;
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

    $sku_price_block = $this->skuPriceHelper->getPriceBlockForSku($sku, ['langcode' => $node->language()->getId()]);
    $object['rendered_price'] = $this->renderer->renderPlain($sku_price_block);

    // Restore the language manager to it's original language.
    $this->languageManager->setConfigOverrideLanguage($original_language);
    $prices = $this->skuManager->getMinPrices($sku, $product_color);
    $object['original_price'] = (float) $prices['price'];
    $object['price'] = (float) $prices['price'];
    $object['final_price'] = (float) $prices['final_price'];

    // Added SKU Fixed price to index object for XB.
    $object['fixed_price'] = $prices['fixed_price'] ?? '';

    // Used for highest discount.
    $object['discount'] = $this->skuManager->getDiscountedPercent($object['price'], $object['final_price']);
    // Use max of selling prices for price in configurable products.
    if (!empty($prices['children'])) {
      $selling_prices = array_filter(array_column($prices['children'], 'selling_price'));
      $object['price'] = max($selling_prices);
      // Use Dicount in configurable products.
      $discount = array_filter(array_column($prices['children'], 'discount'));
      if (empty($discount)) {
        // If Discount is NULL in configurable products set 0.
        $object['discount'] = 0;
      }
      else {
        $object['discount'] = max($discount);
      }
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
      $object['field_configured_skus'] = array_map(fn($item) => $item['value'], $configured_skus);
    }
    // Add value to dummy dummy field attr_delivery_ways.
    // It is depend on status of attr_express_delivery,attr_same_day_delivery.
    $object['attr_delivery_ways'] = [];
    $same_value = 'same_day_delivery_available';
    $express_value = 'express_day_delivery_available';
    if ($sku->get('attr_same_day_delivery')->getString() == '1') {
      array_push($object['attr_delivery_ways'], $same_value);
    }
    if ($sku->get('attr_express_delivery')->getString() == '1') {
      array_push($object['attr_delivery_ways'], $express_value);
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
      $promotion['id'] = $node->get('field_acq_promotion_rule_id')->getString();
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
    // Index the color swatches data.
    $attr_article_swatches = $sku->get('attr_article_swatches')->getString();
    if ($attr_article_swatches) {
      $object['attr_article_swatches'] = json_decode($attr_article_swatches, TRUE);
    }

    $object['url'] = $this->skuInfoHelper->getEntityUrl($node, FALSE);
    // Convert to array to always send key to index event with empty array.
    $object['product_labels'] = (array) $this->skuManager->getLabelsData($sku, 'plp');

    // Update stock info for product.
    $object['stock_quantity'] = $this->skuInfoHelper->calculateStock($sku);
    $object['stock'] = $this->skuManager->getStockStatusForIndex($sku);
    $object['in_stock'] = $object['stock'] === 2 ? 1 : 0;
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
    $object['is_buyable'] = (bool) $sku->get('attr_is_buyable')->getString();
    // Used for new arrivals.
    $object['new_arrivals'] = (int) $sku->get('created')->getString();
    $this->updatePrettyPathAlias($object);
    unset($object['field_category_aliases']);

    // Allow other modules to add/alter object data.
    $this->moduleHandler->alter('alshaya_search_algolia_object', $object);
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
      $field_key = $field_map[$key] ?? $key;

      if (empty($object[$field_key])) {
        continue;
      }

      // Prepare the field name to get the field alias.
      $field_name = str_contains($key, 'attr') || isset($field_map[$key])
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
        'url' => $this->skuImagesHelper->getImageStyleUrl($media_item, SkuImagesHelper::STYLE_PRODUCT_LISTING),
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
      if (str_contains($field_key, 'attr_')) {
        unset($item[$field_key]);
      }
    }
  }

  /**
   * Create term hierarchy to index for Algolia.
   *
   * Prepares the array structure as shown below.
   * phpcs:disable
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
   * phpcs:enable
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
    // Add context value for PLP to use in PLP related index data like swatches.
    $swatches = $this->skuImagesManager->getSwatchData($sku, 'plp');

    if (empty($swatches) || empty($swatches['swatches'])) {
      return [];
    }

    $index_product_image_url = TRUE;
    if (!$display_settings->get('color_swatches_show_product_image')) {
      $index_product_image_url = FALSE;
    }

    $swatch_data = [];
    foreach ($swatches['swatches'] as $key => $swatch) {
      // Check if color swatch is enabled and image url exist.
      if ($index_product_image_url && !empty($swatch['image_url'])) {
        $child = SKU::loadFromSku($swatch['child_sku_code']);

        $swatchUrl = $this->skuImagesManager->getThumbnailImageUrl($child);
        if ($swatchUrl) {
          $swatch['product_image_url'] = $swatchUrl;
        }

        $swatch_data['swatches'][$key] = $swatch;
      }
    }

    return $swatch_data;
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
   * @param string $index_name
   *   The name of the algolia index to add attribute for.
   *
   * @throws \Exception
   *   If attribute is already present in the index, then exception is thrown.
   */
  public function addCustomFacetToIndex($attributes, $index_name = '') {
    $attributes = is_array($attributes) ? $attributes : [$attributes];
    /** @var \Drupal\alshaya_search_algolia\Service\AlshayaAlgoliaIndexHelper $algolia_index */
    $indexNames = !empty($index_name) ? [$index_name] : $this->getAlgoliaIndexNames();
    foreach ($indexNames as $indexName) {
      $search_api_index = 'search_api.index.' . $indexName;
      // @todo If an entity field is to be added, the function should be modified
      // such that $attr_name should have prefix "attr_" as that is the
      // general syntax that can be seen.
      $backend_config = $this->configFactory->get('search_api.server.algolia')->get('backend_config');
      $client_config = $this->configFactory->get($search_api_index)->get('options');
      $client = SearchClient::create($backend_config['application_id'], $backend_config['api_key']);
      $index_name = $client_config['algolia_index_name'];

      if ($client_config['algolia_index_apply_suffix'] == 1) {
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
              $replica_index->setSettings($replica_settings, [
                'forwardToReplicas' => TRUE,
              ]);
            }
          }
        }
      }
      else {
        $updated = FALSE;
        $index = $client->initIndex($index_name);
        $settings = $index->getSettings();
        foreach ($attributes as $attribute_name) {
          if (in_array($attribute_name, $settings['attributesForFaceting'])) {
            $this->logger->error("The attribute $attribute_name is already added to the index.");
            continue;
          }
          // Update Custom Facet attribute
          // for Product list index and its replicas.
          $settings['attributesForFaceting'][] = $attribute_name;
          $updated = TRUE;
        }

        if ($updated) {
          $index->setSettings($settings);

          foreach ($settings['replicas'] as $replica) {
            $replica_index = $client->initIndex($replica);
            $replica_settings = $replica_index->getSettings();
            $replica_settings['attributesForFaceting'] = $settings['attributesForFaceting'];
            $replica_index->setSettings($replica_settings, [
              'forwardToReplicas' => TRUE,
            ]);
          }
        }

      }

      $this->logger->notice('Added attribute(s) for faceting: @attributes', [
        '@attributes' => implode(',', $attributes),
      ]);
    }
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
      $client = SearchClient::create($backend_config['application_id'], $backend_config['api_key']);
      $index_names = $this->getAlgoliaIndexNames();

      // Below variable will contain names of all main index to process.
      $indices = [];

      // Below variable will contain all new replicas for each main index.
      $new_replicas = [];

      foreach ($index_names as $index) {
        $index_config = $this->configFactory->get('search_api.index.' . $index);
        $index_name = $index_config->get('options.algolia_index_name');

        // Get value for algolia_index_apply_suffix in search Api backend.
        $algolia_index_apply_suffix = $index_config->get('options.algolia_index_apply_suffix');

        // Search page.
        if ($algolia_index_apply_suffix == 1) {
          foreach ($this->languageManager->getLanguages() as $language) {
            $final_index_name = $index_name . '_' . $language->getId();
            $new_replicas[$final_index_name] = [];

            foreach ($sorts as $sort) {
              $replica = $final_index_name . '_' . implode('_', $sort);
              $new_replicas[$final_index_name][$replica] = $sort;
            }

            $indices[] = $final_index_name;
          }
        }
        // Algolia V2.
        else {
          // For V2, we have only one main index.
          $indices[] = $index_name;

          $new_replicas[$index_name] = [];

          // Replicas still need language suffix.
          foreach ($this->languageManager->getLanguages() as $language) {
            $final_index_name = $index_name . '_' . $language->getId();
            foreach ($sorts as $sort) {
              $replica = $final_index_name . '_' . implode('_', $sort);
              $new_replicas[$index_name][$replica] = $sort;
            }
          }
        }
      }

      // Update the replicas for all the indices.
      foreach ($indices as $index_name) {
        $index = $client->initIndex($index_name);
        $settings = $index->getSettings();

        $new_replicas_for_index = array_keys($new_replicas[$index_name]);

        // Do nothing if old and new replicas are same.
        if ($new_replicas_for_index === $settings['replicas']) {
          $this->logger->notice('Not updating index replicas as they are already same for index: @index.', [
            '@index' => $index_name,
          ]);

          continue;
        }

        $this->logger->notice('Updating index replicas for index: @index, Replicas: @replicas.', [
          '@index' => $index_name,
          '@replicas' => implode(',', $new_replicas_for_index),
        ]);

        $settings['replicas'] = $new_replicas_for_index;
        $response = $index->setSettings($settings, ['forwardToReplicas' => FALSE]);
        $response->wait();

        $ranking = $settings['ranking'];

        // Update the sort settings for each replica.
        foreach ($new_replicas[$index_name] as $replica => $sort) {
          $replica_index = $client->initIndex($replica);
          $replica_settings = $replica_index->getSettings();
          $replica_settings['ranking'] = [
            'desc(stock)',
            $sort['direction'] . '(' . $sort['field'] . ')',
          ];

          // Allow other modules to add/alter ranking & sorting options.
          $this->moduleHandler->alter('alshaya_search_algolia_ranking_sorting', $replica_settings, $sort, $ranking);

          $replica_settings['ranking'] = $replica_settings['ranking'] + $ranking;
          $response = $replica_index->setSettings($replica_settings, ['forwardToReplicas' => FALSE]);
          $response->wait();
        }
      }
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
   * @param array $sort_label_options
   *   The list of options label.
   * @param array $sort_options
   *   The list of options.
   *
   * @return array
   *   Formated array of sorting options.
   */
  public function prepareFieldsToSort(array $sort_label_options, array $sort_options) {
    $sorts = [];
    foreach ($sort_label_options as $sort_label_option) {
      $value = explode(' ', $sort_label_option['value']);
      if (!empty($sort_label_option['label']) && array_search(trim($value[0]), $sort_options, TRUE)) {
        $sorts[] = ['field' => $value[0], 'direction' => strtolower($value[1])];
      }
    }

    return $sorts;
  }

  /**
   * Helps to return list of Index names.
   */
  public function getAlgoliaIndexNames() {
    $index_name = [];
    $index_name[] = 'alshaya_algolia_index';
    if (AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) {
      $index_name[] = 'alshaya_algolia_product_list_index';
    }
    return $index_name;
  }

  /**
   * Helps to return list of attributes to exclude Language prefix.
   */
  public function getExcludedAttributeList() {
    $excludedAttributes = [
      'objectID',
      'sku',
      'stock',
      'search_api_language',
      'promotion_nid',
      'gtm',
      'nid',
      'search_api_datasource',
      'search_api_id',
      'stock_quantity',
      'attr_aims_barcode',
      'field_configured_skus',
      'media',
    ];

    $this->moduleHandler->alter('alshaya_product_list_exclude_attribute', $excludedAttributes);

    return $excludedAttributes;
  }

  /**
   * Sets algolia index prefixes to use either Drupal or MDC indices.
   *
   * @param string $index_source
   *   Type of index to be set drupal/mdc.
   */
  public function setAlgoliaIndexPrefix($index_source = 'drupal') {
    $index_prefix = NULL;
    $search_settings = $this->configFactory->getEditable('alshaya_search_algolia.settings');

    // Get brand and country.
    global $_acsf_site_name;
    $country = substr($_acsf_site_name, -2);
    if ($index_source === 'drupal') {
      $brand = substr($_acsf_site_name, 0, -2);
      // Use drupal indices.
      $env = mb_strtolower(Settings::get('env'));
      $env = $env === 'travis' ? 'local' : $env;
      $env_number = substr($env, 0, 2);
      if (is_numeric($env_number) && $env_number !== '01') {
        $env = '01' . substr($env, 2);
      }
      // During the production deployment, `01update` env is used and that is
      // not a valid index name prefix, we want to use `01live` only even there.
      if ($env == '01update') {
        $env = '01live';
      }
      // For non-prod env, we have env likes `01dev3up`, `01qaup` which are used
      // during release/deployment. We just remove last `up` from the env name
      // to use the original env. For example - original env for `01dev3up` will
      // be `01dev3`.
      elseif (substr($env, -2) == 'up') {
        $env = substr($env, 0, -2);
      }
      $index_prefix = $env . '_' . $brand . $country;
    }
    elseif ($index_source = 'mdc') {
      // Use MDC indices.
      $algolia_env = Settings::get('algolia_env');
      $index_prefix = $algolia_env . '_' . $country;
    }

    $search_settings->set('index_prefix', $index_prefix)
      ->save();
  }

  /**
   * Get Algolia index prefix.
   *
   * @return string
   *   Returns the drupal/mdc index name.
   */
  public function getAlgoliaIndexPrefix() {
    return $this->configFactory->get('alshaya_search_algolia.settings')->get('index_prefix');
  }

}
