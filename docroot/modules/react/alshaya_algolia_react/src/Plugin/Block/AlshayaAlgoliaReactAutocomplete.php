<?php

namespace Drupal\alshaya_algolia_react\Plugin\Block;

use Drupal\alshaya_algolia_react\AlshayaAlgoliaReactBlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface;
use Drupal\alshaya_acm_product\DeliveryOptionsHelper;

/**
 * Provides a block to display 'autocomplete block' for mobile.
 *
 * @Block(
 *   id = "alshaya_algolia_react_autocomplete",
 *   admin_label = @Translation("Alshaya Algolia Autocomplete")
 * )
 */
class AlshayaAlgoliaReactAutocomplete extends AlshayaAlgoliaReactBlockBase {

  public const PAGE_TYPE = 'search';

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Alshaya Algolia React Config.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface
   */
  protected $alshayaAlgoliaReactConfig;

  /**
   * Delivery Options helper.
   *
   * @var \Drupal\alshaya_acm_product\DeliveryOptionsHelper
   */
  protected $deliveryOptionsHelper;

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
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config
   *   Alshaya Algolia React Config.
   * @param \Drupal\alshaya_acm_product\DeliveryOptionsHelper $delivery_options_helper
   *   Delivery Options Helper.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config,
    DeliveryOptionsHelper $delivery_options_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->alshayaAlgoliaReactConfig = $alshaya_algolia_react_config;
    $this->deliveryOptionsHelper = $delivery_options_helper;
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
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
      $container->get('alshaya_acm_product.delivery_options_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get common configuration for Algolia pages.
    $common_config = $this->alshayaAlgoliaReactConfig->getAlgoliaReactCommonConfig(self::PAGE_TYPE, self::PAGE_TYPE);

    // Get algola settings for lhn menu.
    $config = $this->configFactory->get('alshaya_search_algolia.settings');
    $show_terms_in_lhn = $config->get('show_terms_in_lhn');
    // Menu level is upto L3 when lhn config is all.
    // Default menu level is upto L1.
    $maximum_depth_lhn = ($show_terms_in_lhn == 'all' ? '3' : '1');

    // Add attributes_to_sort_by_name config in drupalSettings.
    $attributes_to_sort_by_name = $this->configFactory
      ->get('alshaya_algolia_react.settings')
      ->get('attributes_to_sort_by_name') ?? [];

    $libraries = [
      'alshaya_algolia_react/autocomplete',
      'alshaya_white_label/algolia_search',
      'alshaya_white_label/slick_css',
    ];

    if ($common_config['commonAlgoliaSearch']['productFrameEnabled'] || $common_config['commonAlgoliaSearch']['promotionFrameEnabled'] || $common_config['commonAlgoliaSearch']['productTitleTrimEnabled']) {
      $libraries[] = 'alshaya_white_label/plp-frame-options';
    }

    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    if ($display_settings->get('color_swatches_show_product_image')) {
      $libraries[] = 'alshaya_white_label/plp-swatch-hover';
    }

    // Get algolia plp related config.
    // Attach quick add css library if feature is enabled.
    $addToBagHoverStatus = $this->configFactory->get('alshaya_algolia_react.settings')->get('add_to_bag_hover');
    if ($addToBagHoverStatus) {
      $libraries[] = 'alshaya_white_label/plp-quick-add';
    }
    // Get common config and merge with new array.
    $algoliaSearchValues = [
      'local_storage_expire' => 15,
      'maximumDepthLhn' => $maximum_depth_lhn,
      'attributes_to_sort_by_name' => $attributes_to_sort_by_name,
    ];
    $autocomplete = $common_config['autocomplete'];
    $reactTeaserView = $common_config['commonReactTeaserView'];
    $commonAlgoliaSearchValues = $common_config['commonAlgoliaSearch'];
    $algoliaSearch = array_merge($commonAlgoliaSearchValues, $algoliaSearchValues);
    $algoliaSearch[self::PAGE_TYPE] = $common_config[self::PAGE_TYPE];

    // Check express day option avialble or not and add drupal settings.
    $express_status = [
      'enabled' => FALSE,
    ];
    if ($this->deliveryOptionsHelper->ifSddEdFeatureEnabled()) {
      $express_status = [
        'enabled' => TRUE,
        'same_day_delivery' => $this->deliveryOptionsHelper->getSameDayDeliveryStatus(),
        'express_delivery' => $this->deliveryOptionsHelper->getExpressDeliveryStatus(),
      ];

      $libraries[] = 'alshaya_white_label/sameday-express-delivery';
    }

    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-autocomplete"></div>',
      '#attached' => [
        'library' => $libraries,
        'drupalSettings' => [
          'algoliaSearch' => $algoliaSearch,
          'autocomplete' => $autocomplete,
          'reactTeaserView' => $reactTeaserView,
          'expressDelivery' => $express_status,
          'addToBagHover' => $addToBagHoverStatus,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['languages'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'config:alshaya_search.settings',
      'config:alshaya_spc.express_delivery',
      'config:alshaya_algolia_react.color_swatches',
    ]);
  }

}
