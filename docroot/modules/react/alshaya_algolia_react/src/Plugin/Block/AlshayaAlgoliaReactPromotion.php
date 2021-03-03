<?php

namespace Drupal\alshaya_algolia_react\Plugin\Block;

use Drupal\alshaya_algolia_react\AlshayaAlgoliaReactBlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a block to display 'promotion' results.
 *
 * @Block(
 *   id = "alshaya_algolia_react_promotion",
 *   admin_label = @Translation("Alshaya Algolia React Promotion")
 * )
 */
class AlshayaAlgoliaReactPromotion extends AlshayaAlgoliaReactBlockBase {

  const PAGE_TYPE = 'listing';

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Alshaya Algolia React Config.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface
   */
  protected $alshayaAlgoliaReactConfig;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Path Validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config,
    RequestStack $requestStack,
    PathValidatorInterface $pathValidator,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaAlgoliaReactConfig = $alshaya_algolia_react_config;
    $this->requestStack = $requestStack;
    $this->pathValidator = $pathValidator;
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
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
      $container->get('request_stack'),
      $container->get('path.validator'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $promotion = $this->getPromotion();
    if (!($promotion instanceof NodeInterface)) {
      return [];
    }

    // Get common configuration for Algolia pages.
    $common_config = $this->alshayaAlgoliaReactConfig->getAlgoliaReactCommonConfig(self::PAGE_TYPE);

    // Get common config and merge with new array.
    $promotion_filters = $common_config[self::PAGE_TYPE]['filters'];
    $algoliaSearchValues = [
      'local_storage_expire' => $common_config['otherRequiredValues']['local_storage_expire'],
      'filters_alias' => array_column($promotion_filters, 'identifier', 'alias'),
      'promotionNodeId' => $promotion->id(),
    ];
    $reactTeaserView = $common_config['commonReactTeaserView'];
    $commonAlgoliaSearchValues = $common_config['commonAlgoliaSearch'];
    $algoliaSearch = array_merge($commonAlgoliaSearchValues, $algoliaSearchValues);
    $algoliaSearch[self::PAGE_TYPE] = $common_config[self::PAGE_TYPE];
    $algoliaSearch['pageSubType'] = 'promotion';

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
   * Get Promotion Node object for current page.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Null if promotion not found.
   */
  protected function getPromotion() {
    static $promotion = NULL;

    if (!isset($promotion)) {
      $promotion = '';

      $url_object = $this->pathValidator->getUrlIfValid(
        $this->requestStack->getCurrentRequest()->getPathInfo()
      );

      if ($url_object && $url_object->getRouteName() == 'entity.node.canonical') {
        $node_id = $url_object->getRouteParameters()['node'];
        $promotion = $node_id
          ? $this->entityTypeManager->getStorage('node')->load($node_id)
          : '';
      }
    }

    return $promotion instanceof NodeInterface
      ? $promotion
      : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = ['alshaya_acm_product_position.settings'];
    Cache::mergeTags(parent::getCacheTags(), $tags);

    $promotion = $this->getPromotion();
    if ($promotion instanceof NodeInterface) {
      $tags = Cache::mergeTags($promotion->getCacheTags(), $tags);
    }

    return $tags;
  }

}
