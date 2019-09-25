<?php

namespace Drupal\alshaya_algolia_react\Plugin\Block;

use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    SkuImagesManager $sku_images_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->skuImagesManager = $sku_images_manager;
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
      $container->get('alshaya_acm_product.sku_images_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $algolia_config = $this->configFactory->get('search_api.server.algolia')->get('backend_config');
    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    $index = $this->configFactory->get('search_api.index.acquia_search_index')->get('options');
    $listing = $this->configFactory->get('alshaya_search_api.listing_settings');
    if ($default_image = $this->skuImagesManager->getProductDefaultImage()) {
      $default_image = ImageStyle::load('product_listing')->buildUrl($default_image->getFileUri());
    }

    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-autocomplete"></div>',
      '#attached' => [
        'library' => [
          'alshaya_algolia_react/autocomplete',
        ],
        'drupalSettings' => [
          'algoliaSearch' => [
            'application_id' => $algolia_config['application_id'],
            'api_key' => $algolia_config['api_key'],
            'indexName' => $index['algolia_index_name'] . "_{$lang}",
            'filterOos' => $listing->get('filter_oos_product'),
            'itemsPerPage' => _alshaya_acm_product_get_items_per_page_on_listing(),
          ],
          'reactTeaserView' => [
            'priceDisplayMode' => $display_settings->get('price_display_mode') ?? SkuPriceHelper::PRICE_DISPLAY_MODE_SIMPLE,
            'gallery' => [
              'showHoverImage' => $display_settings->get('gallery_show_hover_image'),
              'showThumbnails' => $display_settings->get('gallery_show_hover_image') ? FALSE : $display_settings->get('image_thumb_gallery'),
              'defaultImage' => $default_image ?? FALSE,
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

}
