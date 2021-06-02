<?php

namespace Drupal\alshaya_algolia_react\Services;

use Drupal\alshaya_acm_product\AlshayaPromoContextManager;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\alshaya_acm_product_position\AlshayaPlpSortLabelsService;
use Drupal\alshaya_acm_product_position\AlshayaPlpSortOptionsService;
use Drupal\alshaya_custom\AlshayaDynamicConfigValueBase;
use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class AlshayaAlogoliaReactConfig.
 *
 * @package Drupal\alshaya_algolia_react\Service
 */
class AlshayaAlgoliaReactConfig implements AlshayaAlgoliaReactConfigInterface {

  use StringTranslationTrait;

  const FACET_SOURCE = 'search_api:views_page__search__page';

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manger service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Alshaya Promotions Context Manager.
   *
   * @var \Drupal\alshaya_acm_product\AlshayaPromoContextManager
   */
  protected $promoContextManager;

  /**
   * Alshaya Options List Service.
   *
   * @var \Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   Facet manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\alshaya_acm_product_position\AlshayaPlpSortLabelsService $plp_sort_labels
   *   Service to get sort options for PLP.
   * @param \Drupal\alshaya_acm_product_position\AlshayaPlpSortOptionsService $plp_sort_options
   *   Service to get sort option labels for PLP.
   * @param \Drupal\alshaya_acm_product\AlshayaPromoContextManager $alshayaPromoContextManager
   *   Alshaya Promo Context Manager.
   * @param \Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya Options List service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    DefaultFacetManager $facet_manager,
    SkuImagesManager $sku_images_manager,
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaPlpSortLabelsService $plp_sort_labels,
    AlshayaPlpSortOptionsService $plp_sort_options,
    AlshayaPromoContextManager $alshayaPromoContextManager,
    AlshayaOptionsListHelper $alshaya_options_service,
    ModuleHandlerInterface $module_handler
  ) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->facetManager = $facet_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->plpSortLabels = $plp_sort_labels;
    $this->plpSortOptions = $plp_sort_options;
    $this->promoContextManager = $alshayaPromoContextManager;
    $this->alshayaOptionsService = $alshaya_options_service;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('facets.manager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAlgoliaReactCommonConfig(string $page_type) {
    $lang = $this->languageManager->getCurrentLanguage()->getId();

    $index = $this->configFactory->get('search_api.index.alshaya_algolia_index')->get('options');
    // Set Algolia index name from Drupal index eg: 01live_bbwae_en.
    $index_name = $index['algolia_index_name'] . '_' . $lang;
    // Get current index name based on page type.
    if ($page_type === 'listing' && AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) {
      $index = $this->configFactory->get('search_api.index.alshaya_algolia_product_list_index')->get('options');
      // Set Algolia index name from Drupal index
      // eg: 01live_bbwae_product_list.
      $index_name = $index['algolia_index_name'];
    }

    $listing = $this->configFactory->get('alshaya_search_api.listing_settings');
    $currency = $this->configFactory->get('acq_commerce.currency');
    $product_category_settings = $this->configFactory->get('alshaya_acm_product_category.settings');
    // Get Algolia settings for lhn menu.
    $alshaya_algolia_react_setting_values = $this->configFactory->get('alshaya_algolia_react.settings');

    if ($default_image = $this->skuImagesManager->getProductDefaultImage()) {
      $default_image = $this->entityTypeManager
        ->getStorage('image_style')
        ->load('product_listing')
        ->buildUrl($default_image->getFileUri());
    }

    $libraries = [
      'alshaya_algolia_react/plp',
      'alshaya_white_label/slick_css',
    ];

    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    if ($display_settings->get('color_swatches_show_product_image')) {
      $libraries[] = 'alshaya_white_label/promotion-swatch-hover';
    }

    // Add 'back to list' library to search/plp/promo page.
    if ($page_type === 'listing' && $this->configFactory->get('alshaya_acm_product.settings')->get('back_to_list')) {
      $libraries[] = 'alshaya_algolia_react/back_to_plp';
    }

    $response = [];

    $response['commonAlgoliaSearch'] = [
      'application_id' => $alshaya_algolia_react_setting_values->get('application_id'),
      'api_key' => $alshaya_algolia_react_setting_values->get('search_api_key'),
      'filterOos' => $listing->get('filter_oos_product'),
      'itemsPerPage' => $alshaya_algolia_react_setting_values->get('items_per_page') ?? 36,
      'insightsJsUrl' => drupal_get_path('module', 'alshaya_algolia_react') . '/js/algolia/search-insights@1.3.0.min.js',
      'enable_lhn_tree_search' => $product_category_settings->get('enable_lhn_tree_search'),
      'category_facet_label' => $this->t('Category'),
      'sizeGroupSeparator' => SkuManager::SIZE_GROUP_SEPARATOR,
      'productListIndexStatus' => AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index'),
    ];

    $response[$page_type]['filters'] = $this->getFilters($index_name, $page_type);

    $response['autocomplete'] = [
      'hits' => $alshaya_algolia_react_setting_values->get('hits') ?? 4,
      'topResults' => $alshaya_algolia_react_setting_values->get('top_results') ?? 4,
    ];

    $response['otherRequiredValues'] = [
      'lang' => $lang,
      'libraries' => $libraries,
      'local_storage_expire' => $alshaya_algolia_react_setting_values->get('local_storage_expire'),
      'max_category_tree_depth' => $alshaya_algolia_react_setting_values->get('max_category_tree_depth'),
    ];

    $response['commonReactTeaserView'] = [
      'price' => [
        'currency' => $currency->get('currency_code'),
        'currencyPosition' => $currency->get('currency_code_position'),
        'decimalPoints' => $currency->get('decimal_points'),
        'priceDisplayMode' => $display_settings->get('price_display_mode') ?? SkuPriceHelper::PRICE_DISPLAY_MODE_SIMPLE,
      ],
      'gallery' => [
        'showHoverImage' => (bool) $display_settings->get('gallery_show_hover_image'),
        'showThumbnails' => ($display_settings->get('gallery_show_hover_image') === TRUE) ? FALSE : $display_settings->get('image_thumb_gallery'),
        'defaultImage' => $default_image ?? FALSE,
        'lazy_load_placeholder' => $this->configFactory->get('alshaya_master.settings')->get('lazy_load_placeholder'),
        'plp_slider' => $display_settings->get('plp_slider'),
      ],
      'swatches' => [
        'showColorImages' => $display_settings->get('show_color_images_on_filter'),
        'showProductImage' => $display_settings->get('color_swatches_show_product_image'),
        'showVariantsThumbnail' => $display_settings->get('show_variants_thumbnail_plp_gallery'),
        'showSwatches' => $display_settings->get('color_swatches'),
        'swatchPlpLimit' => $display_settings->get('swatch_plp_limit'),
      ],
    ];
    // Allow other modules to alter or add extra configs
    // in agolia react common configurations.
    $this->moduleHandler->alter('algolia_react_common_configs', $response);

    // Add Index name eg: 01live_bbwae_en or 01live_bbwae_product_list
    // for corresponding page-type search / listing.
    $response[$page_type]['indexName'] = $index_name;

    return $response;
  }

  /**
   * Get sort by list options to show.
   *
   * @param string $index_name
   *   The algolia index to use.
   * @param string $page_type
   *   Page Type.
   *
   * @return array
   *   The array of options with key and label.
   */
  protected function getSortByOptions($index_name, $page_type): array {
    if ($page_type === 'search') {
      $position = $this->configFactory->get('alshaya_search.settings');

      $enabled_sorts = array_filter($position->get('sort_options'), function ($item) {
        return ($item != '');
      });

      $labels = AlshayaDynamicConfigValueBase::schemaArrayToKeyValue(
        (array) $position->get('sort_options_labels')
      );

    }
    else {
      $enabled_sorts = $this->plpSortOptions->getCurrentPagePlpSortOptions();

      // Remove un-supported sorting options.
      unset($enabled_sorts['stock_quantity']);

      $labels = $this->plpSortLabels->getSortOptionsLabels();
      $labels = $this->plpSortOptions->sortGivenOptions($labels);
    }

    $sort_items = [];
    foreach ($labels as $label_key => $label_value) {
      if (empty($label_value)) {
        continue;
      }

      $sort_index_key = '';
      [$sort_key, $sort_order] = preg_split('/\s+/', $label_key);

      // We used different keys for listing and search.
      // For now till we completely migrate we will need to do workaround to map
      // them to match the search keys.
      $sort_key_mapping = [
        'name_1' => 'title',
        'nid' => 'search_api_relevance',
      ];

      $index_sort_key = $sort_key_mapping[$sort_key] ?? $sort_key;

      if ($index_sort_key == 'search_api_relevance') {
        $sort_index_key = $index_name;
      }
      elseif (in_array($sort_key, $enabled_sorts)) {
        $sort_index_key = $index_name . '_' . $index_sort_key . '_' . strtolower($sort_order);
        // Get index name by page type.
        if ($page_type === 'listing' && AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) {
          // Get sort index name for listing
          // eg: 01live_bbwae_product_list_en_title_asc.
          $sort_index_key = $index_name . '_'
            . $this->languageManager->getCurrentLanguage()->getId() . '_'
            . $index_sort_key . '_'
            . strtolower($sort_order);
        }
      }

      if (!empty($sort_index_key)) {
        $sort_items[] = [
          'value' => $sort_index_key,
          'label' => $label_value,
        ];
      }

    }
    return $sort_items;
  }

  /**
   * Return filters to show for current site.
   *
   * The function gets all the filters for the existing search api
   * self::FACET_SOURCE, and as we don't have any configuration which facets
   * to show for search, we are removing 'field_category', 'attr_selling_price'
   * and 'attr_color' if 'attr_color_family' is also available (For H&M case).
   *
   * @param string $index_name
   *   The current algolia index.
   * @param string $page_type
   *   Page Type.
   *
   * @return array
   *   Return array of filters.
   *
   * @todo this is temporary way to get filters, work on it to make something
   * solid on which we can rely.
   */
  protected function getFilters($index_name, $page_type) {
    $filter_facets = [
      'sort_by' => [
        'identifier' => 'sort_by',
        'name' => $this->t('Sort by'),
        'label' => $this->t('Sort by'),
        'widget' => [
          'type' => 'sort_by',
          'items' => $this->getSortByOptions($index_name, $page_type),
        ],
        // We want to display filters on the first position.
        'weight' => -100,
      ],
    ];

    $facets = $this->facetManager->getFacetsByFacetSourceId(self::FACET_SOURCE);
    if (!empty($facets)) {
      foreach ($facets as $facet) {
        $block_id = str_replace('_', '', $facet->id());
        $block = $this->entityTypeManager->getStorage('block')->load($block_id);
        if ($block instanceof BlockInterface && !$block->status()) {
          continue;
        }
        $visibility = $block->getVisibility();
        if (isset($visibility['request_path']['pages']) && stripos($visibility['request_path']['pages'], '/search') === FALSE) {
          continue;
        }

        if (!in_array($facet->getFieldIdentifier(), ['attr_selling_price'])) {
          $identifier = $this->identifireSuffixUpdate($facet->getFieldIdentifier(), $page_type);
          $widget = $facet->getWidget();

          if ($facet->getFieldIdentifier() === 'field_category') {
            // For category we have index hierarchy in field_category
            // so, updating field_name and type for react.
            $identifier = $this->identifireSuffixUpdate('field_category', $page_type);
            $widget['type'] = 'hierarchy';
          }
          elseif ($facet->getFieldIdentifier() === 'field_acq_promotion_label') {
            $context = $this->promoContextManager->getPromotionContext();
            $identifier = $this->identifireSuffixUpdate("field_acq_promotion_label.$context", $page_type);
          }

          $facet_values = [];
          if ($widget['type'] === 'swatch_list') {
            $facet_values = $this->loadFacetValues($identifier);
          }

          // For HNM we are using "size_group_list" widget type
          // for size facet. If sizegroup is not enabled then force
          // to make widget type checkbox.
          if ($facet->getFieldIdentifier() == 'attr_size'
              && !$this->configFactory->get('alshaya_acm_product.settings')->get('enable_size_grouping_filter')) {
            $widget['type'] = 'checkbox';
          }

          $filter_facets[explode('.', $identifier)[0]] = [
            'identifier' => $identifier,
            'label' => $block->label(),
            'name' => $facet->getName(),
            'widget' => $widget,
            'id' => $block_id,
            'weight' => $block->getWeight(),
            'alias' => $facet->getUrlAlias(),
            'facet_values' => $facet_values,
          ];
        }
      }
    }

    if ($this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      $filter_facets['super_category'] = [
        'identifier' => 'super_category',
        'name' => $this->t('Super Category'),
        'label' => $this->t('Brands'),
        'widget' => [
          'type' => 'menu',
        ],
        'weight' => 0,
      ];
    }

    // Sort facets by weight.
    uasort($filter_facets, function ($a, $b) {
      if ($a['weight'] == $b['weight']) {
        return 0;
      }
      return ($a['weight'] < $b['weight']) ? -1 : 1;
    });

    return $filter_facets;
  }

  /**
   * Wrapper function to load facet values for an attribute.
   *
   * @param string $attribute
   *   Attribute to load the facet values for.
   *
   * @return array
   *   Facet values.
   */
  protected function loadFacetValues(string $attribute) {
    static $facet_values;

    if (empty($facet_values[$attribute])) {
      $result = $this->alshayaOptionsService->loadFacetsData([
        str_replace('attr_', '', $attribute),
      ]);

      if (!empty($result) && is_array($result)) {
        foreach (array_values($result)[0] as $value) {
          $facet_values[$attribute][explode(',', $value)[0]] = $value;
        }
      }
    }

    return $facet_values[$attribute] ?? [];
  }

  /**
   * Function to update idensitfier with language suffix.
   *
   * @param string $fieldIdentifier
   *   Attribute to identify FieldIdentifier.
   * @param string $page_type
   *   Attribute to indentify page Type.
   *
   * @return string
   *   Facet identifier.
   */
  protected function identifireSuffixUpdate($fieldIdentifier, $page_type) {
    $identifier = $fieldIdentifier;
    // Change Identifier based on Page Type.
    if ($page_type === 'listing' && AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) {
      $identifier = $fieldIdentifier . '.'
        . $this->languageManager->getCurrentLanguage()->getId();
    }
    return $identifier;
  }

}
