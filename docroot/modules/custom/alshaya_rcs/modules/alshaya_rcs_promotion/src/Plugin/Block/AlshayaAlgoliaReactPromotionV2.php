<?php

namespace Drupal\alshaya_rcs_promotion\Plugin\Block;

use Drupal\alshaya_algolia_react\AlshayaAlgoliaReactBlockBase;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface;

/**
 * Provides a block to display 'promotion' results.
 *
 * @Block(
 *   id = "alshaya_algolia_react_promotion_v2",
 *   admin_label = @Translation("Alshaya Algolia React Promotion V2")
 * )
 */
class AlshayaAlgoliaReactPromotionV2 extends AlshayaAlgoliaReactBlockBase {

  public const PAGE_TYPE = 'listing';
  public const PAGE_SUB_TYPE = 'promotion';

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
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get common configuration for Algolia pages.
    $common_config = $this->alshayaAlgoliaReactConfig->getAlgoliaReactCommonConfig(self::PAGE_TYPE, self::PAGE_SUB_TYPE);

    // Get common config and merge with new array.
    $promotion_filters = $common_config[self::PAGE_TYPE]['filters'];
    $algoliaSearchValues = [
      'local_storage_expire' => $common_config['otherRequiredValues']['local_storage_expire'],
      'filters_alias' => array_column($promotion_filters, 'identifier', 'alias'),
    ];
    $reactTeaserView = $common_config['commonReactTeaserView'];
    $commonAlgoliaSearchValues = $common_config['commonAlgoliaSearch'];
    $algoliaSearch = array_merge($commonAlgoliaSearchValues, $algoliaSearchValues);
    $algoliaSearch[self::PAGE_TYPE] = $common_config[self::PAGE_TYPE];
    $algoliaSearch['pageSubType'] = self::PAGE_SUB_TYPE;

    // Add renderer library of promotion to the block.
    array_push(
      $common_config['otherRequiredValues']['libraries'],
      'alshaya_rcs_promotion/renderer',
      'alshaya_algolia_react/plpv2',
    );

    // Remove the v1 PLP library.
    $libraries = array_diff($common_config['otherRequiredValues']['libraries'], ['alshaya_algolia_react/plp']);

    // Add helpers.
    $libraries[] = 'alshaya_rcs_promotion/helpers';

    return [
      'inside' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => 'alshaya-algolia-plp',
        ],
        '#attached' => [
          'library' => $libraries,
          'drupalSettings' => [
            'algoliaSearch' => $algoliaSearch,
            'reactTeaserView' => $reactTeaserView,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = ['alshaya_acm_product_position.settings'];
    Cache::mergeTags(parent::getCacheTags(), $tags);
    return $tags;
  }

}
