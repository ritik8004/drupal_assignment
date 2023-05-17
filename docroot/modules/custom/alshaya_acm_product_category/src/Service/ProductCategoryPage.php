<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\Core\Cache\Cache;
use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactHelper;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Product category term page service.
 */
class ProductCategoryPage {
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
   * The language manger service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Algolia react helper.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactHelper
   */
  protected $algoliaReactHelper;

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
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_manager,
    PathValidatorInterface $pathValidator,
    RequestStack $requestStack,
    EntityRepositoryInterface $entity_repository,
    LanguageManagerInterface $language_manager,
    ConfigFactory $config_factory,
    CacheBackendInterface $cache
  ) {
    $this->entityTypeManager = $entity_manager;
    $this->pathValidator = $pathValidator;
    $this->requestStack = $requestStack;
    $this->entityRepository = $entity_repository;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->cache = $cache;
  }

  /**
   * Initiate algolia helper service.
   *
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactHelper $algolia_react_helper
   *   Algolia react helper.
   */
  public function setAlgoliaReactHelper(AlshayaAlgoliaReactHelper $algolia_react_helper) {
    $this->algoliaReactHelper = $algolia_react_helper;
  }

  /**
   * Get the taxonomy term for the PLP page from route.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Return taxonomy term or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getTermForRoute() {
    // Rely on the Request object to get the taxonomy term ids as views
    // arguments rather than Route matcher service. In case of AJAX requests
    // populating the facets, the arguments don't get populated leading to empty
    // facets on PLP/Promotion detail page post AJAX request.
    $url = $this->requestStack->getCurrentRequest()->getPathInfo();
    $url = explode('--', $url)[0];

    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    if (($url_object = $this->pathValidator->getUrlIfValid($url))
        && (($url_object->getRouteName() === 'entity.taxonomy_term.canonical')
        || ($url_object->getRouteName() === 'alshaya_main_menu.category_view_all'))
        && ($taxonomy_tid = $url_object->getRouteParameters()['taxonomy_term'])
        && (($taxonomy_term = $term_storage->load($taxonomy_tid)) instanceof TermInterface)
    ) {
      return $taxonomy_term;
    }
    return NULL;
  }

  /**
   * Return the string of term hierarchy and nested level count for the term.
   *
   * @param string|null $langcode
   *   The language code to return the string.
   * @param string $tid
   *   The term id.
   *
   * @return array
   *   The array containing hierarchy, level and rule contexts.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCurrentSelectedCategory(string $langcode = NULL, string $tid = '') {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    /** @var \Drupal\taxonomy\TermStorage */
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $term = empty($tid) ? $term = $this->getTermForRoute() : $storage->load($tid);

    // If /taxonomy/term/tid page.
    if (!$term) {
      return [
        'hierarchy' => '',
        'level' => 0,
        'ruleContext' => [],
        'field' => '',
      ];
    }

    $cid = "selected_category_hierarchy:" . $langcode . ":" . $term->id();

    if ($selected_term_data = $this->cache->get($cid)) {
      return $selected_term_data->data;
    }

    $cache_tags = [
      'taxonomy_term:' . $term->id(),
    ];

    $parents = array_reverse($storage->loadAllParents($term->id()));
    $hierarchy_list = [];
    $context_list = [];
    $contexts = [];

    foreach ($parents as $term) {
      $term = $this->entityRepository->getTranslationFromContext($term, $langcode);
      $term_en = ($langcode != 'en')
        ? $this->entityRepository->getTranslationFromContext($term, 'en')
        : $term;

      $hierarchy_list[] = $term->label();

      $context_list[] = $this->algoliaReactHelper->formatCleanRuleContext($term_en->label());

      // Merge term name for to use multiple contexts for category pages.
      $contexts[] = implode('__', $context_list);

      $cache_tags = Cache::mergeTags($cache_tags, $term->getCacheTags());
    }

    // Reverse context list.
    $contexts = array_reverse($contexts);
    // Add prefix "web" to every context value.
    $web_contexts = [];
    foreach ($contexts as $context_item) {
      $web_contexts[] = "web__$context_item";
    }
    // Combine contexts and web contexts.
    $all_contexts = array_merge($contexts, $web_contexts);

    $data = [
      'hierarchy' => implode(' > ', $hierarchy_list),
      'level' => count($contexts),
      'ruleContext' => $all_contexts,
      'category_field' => 'field_category_name.lvl' . (count($contexts) - 1),
    ];

    $this->cache->set($cid, $data, Cache::PERMANENT, $cache_tags);

    return $data;
  }

  /**
   * Centralized place to store the conditions for query to Algolia PLP index.
   *
   * @param string $langcode
   *   The langcode.
   * @param string $category_id
   *   The PLP term id.
   *
   * @return array
   *   Array of search results keyed by the attributes provided.
   */
  public function getPlpSearchQueryData(string $langcode, string $category_id = '') {
    $term_details = $this->getCurrentSelectedCategory($langcode, $category_id);
    $exclude_oos = $this->configFactory->get('alshaya_search_api.listing_settings')->get('filter_oos_product');

    // Set the conditions.
    $conditions = [
      [
        'conditions' => [
          [
            $term_details['category_field'],
            '"' . $term_details['hierarchy'] . '"',
            '=',
          ],
        ],
        'operator' => 'AND',
      ],
    ];
    if ($exclude_oos) {
      $conditions[0]['conditions'][] = ['stock', 0, '>'];
    }

    // Set the options.
    $options = [
      'ruleContexts' => $term_details['ruleContext'],
    ];

    if ($this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($category_id);
      $super_category_term = _alshaya_super_category_get_super_category_for_term($term, $this->languageManager->getCurrentLanguage()->getId());
      $options['optionalFilters'][] = AlshayaSuperCategoryManager::SEARCH_FACET_NAME . ':"' . $super_category_term->label() . '"';
    }
    else {
      $options['optionalFilters'][] = AlshayaSuperCategoryManager::SEARCH_FACET_NAME . ':"' . $term_details['name'] . '"';
    }

    return [
      'condition_groups' => $conditions,
      'options' => $options,
    ];
  }

}
