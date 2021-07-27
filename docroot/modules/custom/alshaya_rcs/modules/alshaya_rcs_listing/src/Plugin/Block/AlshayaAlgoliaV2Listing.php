<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\alshaya_acm_product_category\Service\ProductCategoryPage;
use Drupal\alshaya_algolia_react\AlshayaAlgoliaReactBlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a block to display 'plp' results.
 *
 * @Block(
 *   id = "alshaya_algolia_listing_v2",
 *   admin_label = @Translation("Alshaya Algolia Listing V2")
 * )
 */
class AlshayaAlgoliaV2Listing extends AlshayaAlgoliaReactBlockBase {

  const PAGE_TYPE = 'listing';
  const PAGE_SUB_TYPE = 'plp';

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Product category page service.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage
   */
  protected $productCategoryPage;

  /**
   * The Alshaya Algolia React Config.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface
   */
  protected $alshayaAlgoliaReactConfig;

  /**
   * Entity Repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * AlshayaAlgoliaReactAutocomplete constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page
   *   Product category page service.
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config
   *   Alshaya Algolia React Config.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ProductCategoryPage $product_category_page,
    AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entityRepository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryPage = $product_category_page;
    $this->alshayaAlgoliaReactConfig = $alshaya_algolia_react_config;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->entityRepository = $entityRepository;
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
      $container->get('alshaya_acm_product_category.page'),
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
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

    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-v2-algolia-plp"></div>',
      '#attached' => [
        'library' => [
          'alshaya_algolia_react/plpv2',
          'alshaya_rcs_listing/renderer',
        ],
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
