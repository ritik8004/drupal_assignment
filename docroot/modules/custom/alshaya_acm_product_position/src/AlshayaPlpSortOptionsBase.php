<?php

namespace Drupal\alshaya_acm_product_position;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya Plp Sort Options Base.
 */
class AlshayaPlpSortOptionsBase {

  /**
   * Config id.
   *
   * @see alshaya_acm_product_position.settings in alshaya_pb_transac.
   */
  public const CONFIG_SORT_OPTIONS = 'alshaya_acm_product_position.settings';

  /**
   * Vocabulary of plp page.
   */
  public const VOCABULARY_ID = 'acq_product_category';

  /**
   * Mapping for route name and parameter.
   */
  public const TERM_ROUTE_PARAM = [
    'entity.taxonomy_term.canonical' => 'taxonomy_term',
    'rest.category_product_list.GET' => 'id',
  ];

  /**
   * Mapping for fields for options and label to get settings.
   */
  public const SORT_OPTIONS_SETTINGS = [
    'options' => [
      'type' => 'field_sorting_options',
      'value' => 'field_sorting_order',
    ],
    'labels' => [
      'type' => 'field_sorting_labels',
      'value' => 'field_sort_options_labels',
    ],
  ];

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Read-only Config data for alshaya_acm_product.fields_labels_n_error.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $configSortOptions;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * AlshayaPlpSortOptionsBase constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request_stack,
    LanguageManagerInterface $language_manager,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->configSortOptions = $config_factory->get(self::CONFIG_SORT_OPTIONS);
    $this->routeMatch = $route_match;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->requestStack = $request_stack;
    $this->currentLanguage = $language_manager->getCurrentLanguage()->getId();
    $this->entityRepository = $entity_repository;
  }

  /**
   * Get taxonomy term from current route.
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   *   Return taxonomy term object when available else null.
   */
  protected function getTermForRoute() {
    if (($route_name = $this->routeMatch->getRouteName())
      && in_array($route_name, [
        'entity.taxonomy_term.canonical',
        'rest.category_product_list.GET',
        'views.ajax',
      ])
    ) {
      $term = NULL;
      if ($route_name == 'views.ajax') {
        $views_args = UrlHelper::parse($this->requestStack->getCurrentRequest()->getRequestUri());
        if ($views_args['query']['view_name'] == 'alshaya_product_list' && !empty($views_args['query']['view_args'])) {
          $term = $views_args['query']['view_args'];
        }
      }
      else {
        /** @var \Drupal\taxonomy\TermInterface $route_parameter_value */
        $term = $this->routeMatch->getParameter(self::TERM_ROUTE_PARAM[$route_name]);
      }

      if (is_numeric($term)) {
        $term = $this->termStorage->load($term);
      }

      if ($term instanceof Term && $term->bundle() == self::VOCABULARY_ID) {
        return $term;
      }
    }
    return NULL;
  }

  /**
   * Get plp sort option setting from parent term of the given term.
   *
   * @param \Drupal\taxonomy\TermInterface $taxonomy_term
   *   The taxonomy term object.
   * @param string $type
   *   Type of configuration.
   *
   * @return array|null
   *   Return array value.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getPlpSortConfigForTerm(TermInterface $taxonomy_term, $type = 'options') : ?array {
    $sorting_options = $taxonomy_term->get(self::SORT_OPTIONS_SETTINGS[$type]['type'])->getString();

    if ($sorting_options == 'override'
        && $sorting_options = $taxonomy_term->get(self::SORT_OPTIONS_SETTINGS[$type]['value'])->getString()
    ) {
      // phpcs:ignore
      return unserialize($sorting_options);
    }
    elseif ($sorting_options == 'inherit_category') {
      // Get parent term to inherit sort config.
      $parent_terms = $this->termStorage->loadParents($taxonomy_term->id());
      $term = reset($parent_terms);
      if ($term instanceof Term) {
        if ($term->language()->getId() != $this->currentLanguage && $term->hasTranslation($this->currentLanguage)) {
          $term = $this->entityRepository->getTranslationFromContext($term, $this->currentLanguage);
        }
        return $this->getPlpSortConfigForTerm($term, $type);
      }
      return NULL;
    }
    return NULL;
  }

}
