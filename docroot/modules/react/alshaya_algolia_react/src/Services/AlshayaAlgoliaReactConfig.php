<?php

namespace Drupal\alshaya_algolia_react\Services;

use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\alshaya_search_algolia\Helper\AlshayaAlgoliaSortHelper;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;
use Drupal\alshaya_acm_product_position\AlshayaPlpSortLabelsService;
use Drupal\alshaya_acm_product_position\AlshayaPlpSortOptionsService;
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
use Drupal\alshaya_acm_product\DeliveryOptionsHelper;

/**
 * Class AlshayaAlogoliaReactConfig.
 *
 * @package Drupal\alshaya_algolia_react\Service
 */
class AlshayaAlgoliaReactConfig implements AlshayaAlgoliaReactConfigInterface {

  use StringTranslationTrait;

  public const FACET_SOURCE = 'search_api:views_page__search__page';

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
   * Alshaya Request Context Manager.
   *
   * @var \Drupal\alshaya_acm_product\AlshayaRequestContextManager
   */
  protected $requestContextManager;

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
   * Delivery Options helper.
   *
   * @var \Drupal\alshaya_acm_product\DeliveryOptionsHelper
   */
  protected $deliveryOptionsHelper;

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
   * @param \Drupal\alshaya_acm_product\AlshayaRequestContextManager $alshayaRequestContextManager
   *   Alshaya Request Context Manager.
   * @param \Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya Options List service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\alshaya_acm_product\DeliveryOptionsHelper $delivery_options_helper
   *   Delivery Options Helper.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    DefaultFacetManager $facet_manager,
    SkuImagesManager $sku_images_manager,
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaPlpSortLabelsService $plp_sort_labels,
    AlshayaPlpSortOptionsService $plp_sort_options,
    AlshayaRequestContextManager $alshayaRequestContextManager,
    AlshayaOptionsListHelper $alshaya_options_service,
    ModuleHandlerInterface $module_handler,
    DeliveryOptionsHelper $delivery_options_helper
  ) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->facetManager = $facet_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->plpSortLabels = $plp_sort_labels;
    $this->plpSortOptions = $plp_sort_options;
    $this->requestContextManager = $alshayaRequestContextManager;
    $this->alshayaOptionsService = $alshaya_options_service;
    $this->moduleHandler = $module_handler;
    $this->deliveryOptionsHelper = $delivery_options_helper;
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
      $container->get('module_handler'),
      $container->get('alshaya_acm_product.delivery_options_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAlgoliaReactCommonConfig(string $page_type, string $sub_page = '') {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    // Get algolia index name.
    $index_name = AlshayaAlgoliaSortHelper::getAlgoliaIndexName($lang, $page_type);
    $listing = $this->configFactory->get('alshaya_search_api.listing_settings');
    $currency = $this->configFactory->get('acq_commerce.currency');
    $product_category_settings = $this->configFactory->get('alshaya_acm_product_category.settings');
    // Get Algolia settings for lhn menu.
    $alshaya_algolia_react_setting_values = $this->configFactory->get('alshaya_algolia_react.settings');

    // Get Algolia color swatches settings.
    $algolia_color_swatches_settings = $this->configFactory->get('alshaya_algolia_react.color_swatches');

    // Get listing page frames settings.
    $product_frame_settings = $this->configFactory->get('alshaya_algolia_react.product_frames');

    // Get Algolia Swipe image setting.
    $algolia_swipe_image_settings = $this->configFactory->get('alshaya_algolia_react.swipe_image');

    if ($default_image = $this->skuImagesManager->getProductDefaultImage()) {
      $default_image = $this->entityTypeManager
        ->getStorage('image_style')
        ->load(SkuImagesHelper::STYLE_PRODUCT_LISTING)
        ->buildUrl($default_image->getFileUri());
    }

    $libraries = [
      'alshaya_algolia_react/plp',
      'alshaya_algolia_react/plp_group_by_subcategory',
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
      'productFrameEnabled' => $product_frame_settings->get('product_frame'),
      'promotionFrameEnabled' => $product_frame_settings->get('promotion_frame'),
      'productTitleTrimEnabled' => $product_frame_settings->get('product_title_trim'),
      'productElementAlignmentEnabled' => FALSE,
      'hideGridToggle' => $alshaya_algolia_react_setting_values->get('hide_grid_toggle') ?? 0,
      'topFacetsLimit' => $alshaya_algolia_react_setting_values->get('top_facets_limit'),
      'defaultColgrid' => $alshaya_algolia_react_setting_values->get('default_col_grid'),
      'defaultColGridMobile' => $alshaya_algolia_react_setting_values->get('default_col_grid_mobile'),
      'hitsPerPage' => $alshaya_algolia_react_setting_values->get('enable_hits_per_page'),
      'renderSingleResultFacets' => $alshaya_algolia_react_setting_values->get('render_single_result_facets'),
      'excludeRenderSingleResultFacets' => $alshaya_algolia_react_setting_values->get('exclude_render_single_result_facets'),
      'plpTeaserAttributes' => $alshaya_algolia_react_setting_values->get('product_teaser_attributes'),
    ];

    // Set product elements alignment to true only
    // when all three options i.e promotion frame,
    // product title trim and product element alignment are enabled.
    if ($product_frame_settings->get('promotion_frame')
      && $product_frame_settings->get('product_title_trim')
      && $product_frame_settings->get('product_element_alignment')) {
      $response['commonAlgoliaSearch']['productElementAlignmentEnabled'] = TRUE;
    }

    $response[$page_type]['filters'] = $this->getFilters($index_name, $page_type, $sub_page);

    // Add library for multilevel_widget eg: Bra Size.
    foreach ($response[$page_type]['filters'] as $facet) {
      if ($facet['widget']['type'] === 'multi_level_widget') {
        $libraries[] = 'alshaya_white_label/multi-level-widget';
        break;
      }
    }
  
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
    $show_color_swatch_slider = (bool) $algolia_color_swatches_settings->get('enable_listing_page_color_swatch_slider');
    $swatch_plp_limit = $show_color_swatch_slider
      ? $algolia_color_swatches_settings->get('no_of_swatches_desktop')
      : $display_settings->get('swatch_plp_limit');
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
        'plp_slider' => $display_settings->get('plp_slider'),
      ],
      'swatches' => [
        'showColorImages' => $display_settings->get('show_color_images_on_filter'),
        'showProductImage' => $display_settings->get('color_swatches_show_product_image'),
        'showVariantsThumbnail' => $display_settings->get('show_variants_thumbnail_plp_gallery'),
        'showSwatches' => $display_settings->get('color_swatches'),
        'showSliderSwatch' => $display_settings->get('show_variants_thumbnail_plp_gallery2'),
        'swatchPlpLimit' => $swatch_plp_limit,
        'swatchPlpLimitMobileView' => $algolia_color_swatches_settings->get('no_of_swatches_mobile'),
        'showArticleSwatches' => $alshaya_algolia_react_setting_values->get('show_article_swatches'),
        'articleSwatchType' => $algolia_color_swatches_settings->get('swatch_type'),
        'showColorSwatchSlider' => $show_color_swatch_slider,
      ],
      'showBrandName' => $display_settings->get('show_brand_name_plp'),
      'swipeImage' => [
        'enableSwipeImageMobile' => $algolia_swipe_image_settings->get('enable_swipe_image_mobile'),
        'noOfImageScroll' => $algolia_swipe_image_settings->get('no_of_image_scroll'),
        'slideEffect' => $algolia_swipe_image_settings->get('slide_effect'),
        'imageSlideTiming' => $algolia_swipe_image_settings->get('image_slide_timing'),
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
   * @param string $sub_page_type
   *   Sub Page Type.
   *
   * @return array
   *   Return array of filters.
   *
   * @todo this is temporary way to get filters, work on it to make something
   * solid on which we can rely.
   */
  protected function getFilters($index_name, $page_type, $sub_page_type) {
    $filter_facets = [
      'sort_by' => [
        'identifier' => 'sort_by',
        'name' => $this->t('Sort by'),
        'label' => $this->t('Sort by'),
        'widget' => [
          'type' => 'sort_by',
          'items' => AlshayaAlgoliaSortHelper::getSortByOptions($index_name, $page_type),
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
        // Checks for alshaya_listing_page_types in the config.
        // Checks if $sub_page_type has value.
        if (isset($visibility['alshaya_listing_page_types'])
          && !empty($sub_page_type)
          && array_key_exists($sub_page_type, $visibility['alshaya_listing_page_types']['page_types'])) {
          // Returns to the beginning if
          // show_on_selected_pages is null or not set to 1.
          // sub_page_type is not available.
          // the sub_page_type is not selected.
          $show_on_pages = $visibility['alshaya_listing_page_types']['show_on_selected_pages'];
          $sub_page_type_selected = $visibility['alshaya_listing_page_types']['page_types'][$sub_page_type];
          if (($show_on_pages === '1' && $sub_page_type_selected !== 1)
            || ($show_on_pages !== '1' && $sub_page_type_selected === 1)) {
            continue;
          }
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
            $context = $this->requestContextManager->getContext();
            $identifier = $this->identifireSuffixUpdate("field_acq_promotion_label", $page_type) . '.' . $context;
          }

          $facet_values = [];
          if ($widget['type'] === 'swatch_list') {
            $facet_values = $this->loadFacetValues($identifier, $page_type);
          }

          $same_value = NULL;
          $express_value = NULL;
          if ($widget['type'] === 'delivery_ways') {
            // If feature enabled then only show facet.
            if (!($this->deliveryOptionsHelper->ifSddEdFeatureEnabled())) {
              continue;
            }
            $identifier = $this->identifireSuffixUpdate('attr_delivery_ways', $page_type);
            $langcode = $this->languageManager->getCurrentLanguage()->getId();
            $same_value = $this->t(
              'Same Day Delivery Available',
              [],
              [$langcode,
                'context' => 'same_day_delivery_listing',
              ]
            );
            $express_value = $this->t(
              'Express Delivery Available',
              [],
              [$langcode,
                'context' => 'express_day_delivery_listing',
              ]
            );
            $facet_values = $this->loadFacetValues($identifier, $page_type);
            if (isset($facet_values['express_day_delivery_available'])) {
              $facet_values['express_day_delivery_available'] = $express_value . ',express_delivery';
            }
            if (isset($facet_values['same_day_delivery_available'])) {
              $facet_values['same_day_delivery_available'] = $same_value . ',same_day_delivery';
            }
          }

          // For HNM we are using "size_group_list" widget type
          // for size facet. If sizegroup is not enabled then force
          // to make widget type checkbox.
          if ($facet->getFieldIdentifier() == 'attr_size'
              && !$this->configFactory->get('alshaya_acm_product.settings')->get('enable_size_grouping_filter')) {
            $widget['type'] = 'checkbox';
          }

          $filterKey = explode('.', $identifier)[0];
          $filter_facets[$filterKey] = [
            'identifier' => $identifier,
            'label' => $block->label(),
            'name' => $facet->getName(),
            'widget' => $widget,
            'id' => $block_id,
            'weight' => $block->getWeight(),
            'alias' => $facet->getUrlAlias(),
            'facet_values' => $facet_values,
          ];

          if ($widget['type'] === 'delivery_ways') {
            // If feature enabled then only show facet.
            if (!($this->deliveryOptionsHelper->ifSddEdFeatureEnabled())) {
              continue;
            }
            $filter_facets[$filterKey]['same_value'] = $same_value . ',same_day_delivery';
            $filter_facets[$filterKey]['express_value'] = $express_value . ',express_delivery';
          }
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
    uasort($filter_facets, fn($a, $b) => $a['weight'] <=> $b['weight']);

    return $filter_facets;
  }

  /**
   * Wrapper function to load facet values for an attribute.
   *
   * @param string $attribute
   *   Attribute to load the facet values for.
   * @param string $page_type
   *   Attribute to indentify page Type.
   *
   * @return array
   *   Facet values.
   */
  protected function loadFacetValues(string $attribute, $page_type = '') {
    static $facet_values;
    // Remove language suffix of attribute based on Page Type
    // to get attribute codes from acquia_search_index.
    if ($page_type === 'listing' && AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) {
      $attribute = explode('.', $attribute)[0];
    }

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
