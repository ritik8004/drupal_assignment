<?php

namespace Drupal\alshaya_algolia_react\Plugin\Block;

use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    SkuImagesManager $sku_images_manager,
    DefaultFacetManager $facet_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->facetManager = $facet_manager;
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
      $container->get('facets.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $algolia_config = $this->configFactory->get('search_api.server.algolia')->get('backend_config');
    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    // Get current index name.
    $index = $this->configFactory->get('search_api.index.alshaya_algolia_index')->get('options');
    $index_name = $index['algolia_index_name'] . "_{$lang}";
    $listing = $this->configFactory->get('alshaya_search_api.listing_settings');
    if ($default_image = $this->skuImagesManager->getProductDefaultImage()) {
      $default_image = ImageStyle::load('product_listing')->buildUrl($default_image->getFileUri());
    }
    $currency = $this->configFactory->get('acq_commerce.currency');
    $configuration = $this->getConfiguration();

    $filter_facets = [
      [
        'identifier' => 'sort_by',
        'name' => $this->t('Sort By'),
        'widget' => [
          'type' => 'sort_by',
          'items' => [
            ['value' => $index_name, 'label' => $this->t('Featured')],
            ['value' => $index_name . '_created_desc', 'label' => $this->t('New In')],
            ['value' => $index_name . '_title_asc', 'label' => $this->t('Name A to Z')],
            ['value' => $index_name . '_title_desc', 'label' => $this->t('Name Z to A')],
            ['value' => $index_name . '_final_price_desc', 'label' => $this->t('Price High to Low')],
            ['value' => $index_name . '_final_price_asc', 'label' => $this->t('Price Low to High')],
          ],
        ],
      ],
    ];

    $facets = $this->facetManager->getFacetsByFacetSourceId(self::FACET_SOURCE);
    if (!empty($facets)) {
      foreach ($facets as $facet) {
        if (!in_array(
          $facet->getFieldIdentifier(),
          ['field_category', 'attr_color', 'attr_selling_price']
        )) {
          $filter_facets[] = [
            'identifier' => $facet->getFieldIdentifier(),
            'name' => $facet->getName(),
            'widget' => $facet->getWidget(),
          ];
        }
      }
    }

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
            'application_id' => $algolia_config['application_id'],
            'api_key' => $algolia_config['api_key'],
            'indexName' => $index_name,
            'filterOos' => $listing->get('filter_oos_product'),
            'itemsPerPage' => _alshaya_acm_product_get_items_per_page_on_listing(),
            'insightsJsUrl' => drupal_get_path('module', 'alshaya_algolia_react') . '/js/search-insights@1.3.0.min.js',
            'filters' => $filter_facets,
          ],
          'autocomplete' => [
            'hits' => $configuration['hits'],
            'topResults' => $configuration['top_results'],
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
              'plp_slider' => $display_settings->get('plp_slider'),
            ],
            'swatches' => [
              'showColorImages' => $display_settings->get('show_color_images_on_filter'),
              'showProductImage' => $display_settings->get('color_swatches_show_product_image'),
              'showVariantsThumbnail' => $display_settings->get('show_variants_thumbnail_plp_gallery'),
            ],
          ],
        ],
      ],
      '#cache' => [
        'contexts' => ['languages'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

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

    parent::submitConfigurationForm($form, $form_state);
  }

}
