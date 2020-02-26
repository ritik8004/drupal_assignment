<?php

namespace Drupal\alshaya_algolia_react\Plugin\Block;

use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\block\BlockInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display 'autocomplete block' for mobile.
 *
 * @Block(
 *   id = "alshaya_algolia_react_autocomplete",
 *   admin_label = @Translation("Alshaya Algolia Autocomplete")
 * )
 */
class AlshayaAlgoliaReactAutocomplete extends BlockBase implements ContainerFactoryPluginInterface {

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
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * Facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaAlgoliaReactAutocomplete constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   Facet manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    SkuImagesManager $sku_images_manager,
    DefaultFacetManager $facet_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->facetManager = $facet_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('facets.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    // Get current index name.
    $index = $this->configFactory->get('search_api.index.alshaya_algolia_index')->get('options');
    $index_name = $index['algolia_index_name'] . '_' . $lang;
    $listing = $this->configFactory->get('alshaya_search_api.listing_settings');
    if ($default_image = $this->skuImagesManager->getProductDefaultImage()) {
      $default_image = ImageStyle::load('product_listing')->buildUrl($default_image->getFileUri());
    }
    $currency = $this->configFactory->get('acq_commerce.currency');
    $configuration = $this->getConfiguration();
    $product_category_settings = $this->configFactory->get('alshaya_acm_product_category.settings');

    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-autocomplete"></div>',
      '#attached' => [
        'library' => [
          'alshaya_algolia_react/autocomplete',
          'alshaya_white_label/algolia_search',
          'alshaya_white_label/slick_css',
        ],
        'drupalSettings' => [
          'algoliaSearch' => [
            'application_id' => $configuration['application_id'],
            'api_key' => $configuration['search_api_key'],
            'indexName' => $index_name,
            'filterOos' => $listing->get('filter_oos_product'),
            'itemsPerPage' => $configuration['items_per_page'] ?? 12,
            'insightsJsUrl' => drupal_get_path('module', 'alshaya_algolia_react') . '/js/algolia/search-insights@1.3.0.min.js',
            'filters' => $this->getFilters($index_name),
            'enable_lhn_tree_search' => $product_category_settings->get('enable_lhn_tree_search'),
            'category_facet_label' => $this->t('Category'),
          ],
          'autocomplete' => [
            'hits' => $configuration['hits'] ?? 4,
            'topResults' => $configuration['top_results'] ?? 4,
          ],
          'reactTeaserView' => [
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
          ],
        ],
      ],
    ];
  }

  /**
   * Get sort by list options to show.
   *
   * @param string $index_name
   *   The algolia index to use.
   *
   * @return array
   *   The array of options with key and label.
   */
  protected function getSortByOptions($index_name): array {
    $enabled_sorts = array_filter(_alshaya_search_get_config(), function ($item) {
      return ($item != '');
    });
    $labels = _alshaya_search_get_config(TRUE);

    $sort_items = [];
    foreach ($labels as $label_key => $label_value) {
      if (empty($label_value)) {
        continue;
      }

      $sort_index_key = '';
      list($sort_key, $sort_order) = preg_split('/\s+/', $label_key);
      if (in_array($sort_key, $enabled_sorts)) {
        $sort_index_key = $index_name . '_' . $sort_key . '_' . strtolower($sort_order);
      }
      elseif ($sort_key == 'search_api_relevance') {
        $sort_index_key = $index_name;
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
   *
   * @return array
   *   Return array of filters.
   *
   * @todo: this is temporary way to get filters, work on it to make something
   * solid on which we can rely.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getFilters($index_name) {
    $filter_facets = [
      'sort_by' => [
        'identifier' => 'sort_by',
        'name' => $this->t('Sort by'),
        'label' => $this->t('Sort by'),
        'widget' => [
          'type' => 'sort_by',
          'items' => $this->getSortByOptions($index_name),
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
          $widget = $facet->getWidget();
          if ($facet->getFieldIdentifier() === 'field_acq_promotion_label') {
            $identifier = 'promotions';
          }
          elseif ($facet->getFieldIdentifier() === 'field_category') {
            // For category we have index hierarchy in field_category
            // so, updating field_name and type for react.
            $identifier = 'field_category';
            $widget['type'] = 'hierarchy';
          }
          else {
            $identifier = $facet->getFieldIdentifier();
          }

          $filter_facets[$identifier] = [
            'identifier' => $identifier,
            'label' => $block->label(),
            'name' => $facet->getName(),
            'widget' => $widget,
            'id' => $block_id,
            'weight' => $block->getWeight(),
          ];
        }
      }
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
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['application_id'] = [
      '#title' => $this->t('Application id'),
      '#type' => 'textfield',
      '#default_value' => $config['application_id'] ?? '',
      '#required' => TRUE,
    ];

    $form['search_api_key'] = [
      '#title' => $this->t('Search api key.'),
      '#type' => 'textfield',
      '#default_value' => $config['search_api_key'] ?? '',
      '#required' => TRUE,
    ];

    $form['hits'] = [
      '#type' => 'number',
      '#min' => 1,
      '#step' => 1,
      '#max' => 100,
      '#title' => $this->t('Number of results to show'),
      '#default_value' => $config['hits'] ?? 10,
    ];

    $form['top_results'] = [
      '#type' => 'number',
      '#min' => 1,
      '#step' => 1,
      '#max' => 100,
      '#title' => $this->t('Number of top results to show for autocomplete'),
      '#default_value' => $config['top_results'] ?? 10,
    ];

    $form['items_per_page'] = [
      '#type' => 'number',
      '#min' => 1,
      '#step' => 1,
      '#max' => 100,
      '#title' => $this->t('Number of items to show for search results.'),
      '#default_value' => $config['items_per_page'] ?? 12,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getErrors()) {
      return;
    }

    $this->configuration['hits'] = $form_state->getValue('hits');
    $this->configuration['top_results'] = $form_state->getValue('top_results');
    $this->configuration['search_api_key'] = $form_state->getValue('search_api_key');
    $this->configuration['application_id'] = $form_state->getValue('application_id');
    $this->configuration['items_per_page'] = $form_state->getValue('items_per_page');

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'languages',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'config:acq_commerce.currency',
      'config:alshaya_acm_product.display_settings',
      'config:alshaya_search_api.listing_settings',
      'config:alshaya_search.settings',
    ]);
  }

}
