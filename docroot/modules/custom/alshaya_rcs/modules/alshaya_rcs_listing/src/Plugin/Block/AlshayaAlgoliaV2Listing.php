<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\alshaya_algolia_react\AlshayaAlgoliaReactBlockBase;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface;

/**
 * Provides a block to display 'plp' results.
 *
 * @Block(
 *   id = "alshaya_algolia_listing_v2",
 *   admin_label = @Translation("Alshaya Algolia Listing V2")
 * )
 */
class AlshayaAlgoliaV2Listing extends AlshayaAlgoliaReactBlockBase {

  public const PAGE_TYPE = 'listing';
  public const PAGE_SUB_TYPE = 'plp';

  /**
   * The Alshaya Algolia React Config.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface
   */
  protected $alshayaAlgoliaReactConfig;

  /**
   * AlshayaAlgoliaReactAutocomplete constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config
   *   Alshaya Algolia React Config.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaAlgoliaReactConfig = $alshaya_algolia_react_config;
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
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get common configuration for Algolia pages.
    $common_config = $this->alshayaAlgoliaReactConfig->getAlgoliaReactCommonConfig(self::PAGE_TYPE, self::PAGE_SUB_TYPE);
    // Get common config and merge with new array.
    $filters = $common_config[self::PAGE_TYPE]['filters'];

    $algoliaSearchValues = [
      'local_storage_expire' => $common_config['otherRequiredValues']['local_storage_expire'],
      'filters_alias' => array_column($filters, 'identifier', 'alias'),
      'max_category_tree_depth' => $common_config['otherRequiredValues']['max_category_tree_depth'],
    ];

    $reactTeaserView = $common_config['commonReactTeaserView'];
    $commonAlgoliaSearchValues = $common_config['commonAlgoliaSearch'];
    $algoliaSearch = array_merge($commonAlgoliaSearchValues, $algoliaSearchValues);
    $algoliaSearch[self::PAGE_TYPE] = $common_config[self::PAGE_TYPE];
    $algoliaSearch['pageSubType'] = self::PAGE_SUB_TYPE;

    // Add renderer library of PLP to the block.
    array_push(
      $common_config['otherRequiredValues']['libraries'],
      'alshaya_algolia_react/plpv2',
      'alshaya_rcs_listing/renderer',
      'alshaya_white_label/rcs-algolia-plp',
    );

    // Remove the v1 PLP library.
    $libraries = array_diff($common_config['otherRequiredValues']['libraries'], ['alshaya_algolia_react/plp']);

    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-plp"></div>',
      '#attached' => [
        'library' => $libraries,
        'drupalSettings' => [
          'algoliaSearch' => $algoliaSearch,
          'reactTeaserView' => $reactTeaserView,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = ['alshaya_acm_product_position.settings'];
    $tags = Cache::mergeTags(parent::getCacheTags(), $tags);

    return $tags;
  }

}
