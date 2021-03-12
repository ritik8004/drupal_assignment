<?php

namespace Drupal\alshaya_product_list\Plugin\Block;

use Drupal\alshaya_algolia_react\AlshayaAlgoliaReactBlockBase;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface;
use Drupal\alshaya_product_list\Service\AlshayaProductListHelper;

/**
 * Provides a block to display 'products list' results.
 *
 * @Block(
 *   id = "alshaya_algolia_react_product_list",
 *   admin_label = @Translation("Alshaya Algolia React Product List")
 * )
 */
class AlshayaAlgoliaReactProductList extends AlshayaAlgoliaReactBlockBase {

  const PAGE_TYPE = 'listing';

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Alshaya product list helper service.
   *
   * @var \Drupal\alshaya_product_list\Service\AlshayaProductListHelper
   */
  protected $alshayaProductListHelper;

  /**
   * The Alshaya Algolia React Config.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface
   */
  protected $alshayaAlgoliaReactConfig;

  /**
   * AlshayaAlgoliaReactProductList constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_product_list\Service\AlshayaProductListHelper $alshaya_product_list_helper
   *   Alshaya product list helper service.
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config
   *   Alshaya Algolia React Config.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AlshayaProductListHelper $alshaya_product_list_helper,
    AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaProductListHelper = $alshaya_product_list_helper;
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
      $container->get('alshaya_product_list.page_helper'),
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get common configuration for Algolia pages.
    $common_config = $this->alshayaAlgoliaReactConfig->getAlgoliaReactCommonConfig(self::PAGE_TYPE);

    // Get common config and merge with new array.
    $filters = $common_config[self::PAGE_TYPE]['filters'];

    $lang = $common_config['otherRequiredValues']['lang'];
    $options_data = $this->alshayaProductListHelper->getCurrentSelectedProductOption($lang);

    // Rule context will be like - 'brand_list__{brand_name_in_english}'.
    $ruleContext = $options_data['ruleContext'];

    $algoliaSearchValues = [
      'local_storage_expire' => $common_config['otherRequiredValues']['local_storage_expire'],
      'filters_alias' => array_column($filters, 'identifier', 'alias'),
      'option_page' => [
        'option_key' => $options_data['option_key'],
        'option_val' => $options_data['option_val'],
      ],
      'ruleContext' => $ruleContext,
    ];
    $reactTeaserView = $common_config['commonReactTeaserView'];
    $commonAlgoliaSearchValues = $common_config['commonAlgoliaSearch'];
    $algoliaSearch = array_merge($commonAlgoliaSearchValues, $algoliaSearchValues);
    $algoliaSearch[self::PAGE_TYPE] = $common_config[self::PAGE_TYPE];
    $algoliaSearch['pageSubType'] = 'product_option_list';

    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-plp"></div>',
      '#attached' => [
        'library' => $common_config['otherRequiredValues']['libraries'],
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
    return Cache::mergeTags(parent::getCacheTags(), [
      'alshaya_acm_product_position.settings',
    ]);
  }

}
