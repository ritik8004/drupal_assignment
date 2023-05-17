<?php

namespace Drupal\alshaya_product_list\Service;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\block\BlockInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\node\NodeInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Product list helper service.
 */
class AlshayaProductListHelper {

  public const VOCAB_ID = 'acq_product_category';
  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Path Validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The language manger service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The facet manger.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Algolia react helper.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactHelper
   */
  protected $algoliaReactHelper;

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $configFactory;

  /**
   * ProductCategoryTermId constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity type manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   Facet manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Factory.
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactHelper $algolia_react_helper
   *   Algolia react helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_manager,
    PathValidatorInterface $pathValidator,
    RequestStack $requestStack,
    EntityRepositoryInterface $entity_repository,
    LanguageManagerInterface $language_manager,
    DefaultFacetManager $facet_manager,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    AlshayaAlgoliaReactHelper $algolia_react_helper,
    ConfigFactoryInterface $config_factory
  ) {
    $this->entityTypeManager = $entity_manager;
    $this->pathValidator = $pathValidator;
    $this->requestStack = $requestStack;
    $this->entityRepository = $entity_repository;
    $this->languageManager = $language_manager;
    $this->facetManager = $facet_manager;
    $this->algoliaReactHelper = $algolia_react_helper;
    $this->logger = $loggerChannelFactory->get('alshaya_product_list');
    $this->configFactory = $config_factory;
  }

  /**
   * Get the product option for the PLP page from route.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Return product option or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getProductOptionForRoute() {
    // Rely on the Request object to get the optoin key/val from route.
    $url = $this->requestStack->getCurrentRequest()->getPathInfo();

    $storage = $this->entityTypeManager->getStorage('node');
    if (($url_object = $this->pathValidator->getUrlIfValid($url))
      && ($url_object->getRouteName() == 'entity.node.canonical')
      && ($nid = $url_object->getRouteParameters()['node'])
      && (($node = $storage->load($nid)) instanceof NodeInterface)
    ) {
      return $node;
    }
    return NULL;
  }

  /**
   * Return the string of option key and name.
   *
   * @param string|null $langcode
   *   The language code to return the string.
   *
   * @return array
   *   The array containing option_key, option_val.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCurrentSelectedProductOption(string $langcode = NULL) {
    $node = $this->getProductOptionForRoute();

    // If /brand/nid page.
    if (!$node) {
      return [
        'option_key' => '',
        'option_val' => 0,
        'ruleContext' => [],
      ];
    }

    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $node = $this->entityRepository->getTranslationFromContext($node, $langcode);

    // Get english version of the brand node only to prepare the
    // ruleContext for the brand list pages.
    $node_en = ($langcode != 'en')
      ? $this->entityRepository->getTranslationFromContext($node, 'en')
      : $node;

    $context = $this->algoliaReactHelper->formatCleanRuleContext($node_en->label());
    $rule_context = 'brand_list__' . $context;

    return [
      'option_key' => $node->get('field_attribute_name')->first()->getString(),
      'option_val' => $node->get('field_attribute_value')->first()->getString(),
      'ruleContext' => [$rule_context, "web__$rule_context"],
    ];
  }

  /**
   * Toggle algolia status for product list block.
   *
   * @param bool $algolia_product_list_status
   *   Status of block.
   */
  public function toggleAlgoliaProductList($algolia_product_list_status = TRUE) {
    $block_storage = $this->entityTypeManager->getStorage('block');
    $facets = $this->facetManager->getFacetsByFacetSourceId('search_api:views_block__alshaya_product_list__block_3');

    $product_list_block = $block_storage->load('alshayaalgoliareactproductlist');
    if ($product_list_block instanceof BlockInterface && $product_list_block->status()) {
      $this->logger->notice('Algolia on product list is already enabled.');
      return;
    }

    // Install algolia product list block.
    alshaya_config_install_configs(['block.block.alshaya_algolia_react_product_list'], 'alshaya_product_list');

    // Update block weight of algolia react product list block to match
    // what was there for views product list block.
    $existing = \Drupal::configFactory()->get('block.block.views_block__alshaya_product_list_block_3');
    $new = \Drupal::configFactory()->getEditable('block.block.alshaya_algolia_react_product_list');
    $new->set('weight', $existing->get('weight'));
    $new->save();

    // If status is 'enable' we want to push all enabled blocks to config and
    // for enabled status use config data to restore block status.
    if ($algolia_product_list_status) {
      foreach ($facets as $facet) {
        $block_id = str_replace('_', '', $facet->id());
        $facet_block = $block_storage->load($block_id);
        if ($facet_block instanceof BlockInterface) {
          $facet_block->disable();
          $facet_block->save();
        }
      }
    }

    $other_product_list_blocks = $block_storage->loadByProperties(['visibility.entity_bundle:node.bundles.product_list' => 'product_list']);
    foreach ($other_product_list_blocks as $other_product_list_block) {
      if ($other_product_list_block instanceof BlockInterface) {
        $other_product_list_block->setStatus(!$algolia_product_list_status);
        $other_product_list_block->save();
      }
    }

    $product_list_block = $block_storage->load('alshayaalgoliareactproductlist');
    // If database product list block status is disabled Enable,
    // the algolia product list block otherwise disable.
    if ($product_list_block instanceof BlockInterface) {
      $product_list_block->setStatus($algolia_product_list_status);
      $product_list_block->save();
    }
  }

  /**
   * Return the term list in vocab 'acq_product_category'.
   *
   * @return array
   *   Return term list or null.
   */
  public function getVocabListLhnBlock() {
    $vocab_list = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => self::VOCAB_ID,
      'name' => $this->configFactory->get('alshaya_product_list.settings')->get('product_list_lhn_term'),
      'depth_level' => 1,
    ]);
    if (empty($vocab_list)) {
      return [];
    }
    return $vocab_list;
  }

}
