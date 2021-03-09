<?php

namespace Drupal\alshaya_product_list\Service;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\block\BlockInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Product list helper service.
 */
class AlshayaProductListHelper {

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
   */
  public function __construct(
    EntityTypeManagerInterface $entity_manager,
    PathValidatorInterface $pathValidator,
    RequestStack $requestStack,
    EntityRepositoryInterface $entity_repository,
    LanguageManagerInterface $language_manager,
    DefaultFacetManager $facet_manager,
    LoggerChannelFactoryInterface $loggerChannelFactory
  ) {
    $this->entityTypeManager = $entity_manager;
    $this->pathValidator = $pathValidator;
    $this->requestStack = $requestStack;
    $this->entityRepository = $entity_repository;
    $this->languageManager = $language_manager;
    $this->facetManager = $facet_manager;
    $this->logger = $loggerChannelFactory->get('alshaya_product_list');
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

    $context = strtolower(trim($node_en->label()));
    // Remove special characters.
    $context = preg_replace("/[^a-zA-Z0-9\s]/", "", $context);
    // Ensure duplicate spaces are replaced with single space.
    // H & M would have become H  M after preg_replace.
    $context = str_replace('  ', ' ', $context);

    // Replace spaces with underscore.
    $context = str_replace(' ', '_', $context);

    return [
      'option_key' => $node->get('field_attribute_name')->first()->getString(),
      'option_val' => $node->get('field_attribute_value')->first()->getString(),
      'ruleContext' => ['brand_list__' . $context],
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

    $product_list_block = $block_storage->load('alshaya_algolia_react_product_list');
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

    // If database product list block status is disabled Enable,
    // the algolia product list block otherwise disable.
    if ($product_list_block instanceof BlockInterface) {
      $product_list_block->setStatus($algolia_product_list_status);
      $product_list_block->save();
    }
  }

}
