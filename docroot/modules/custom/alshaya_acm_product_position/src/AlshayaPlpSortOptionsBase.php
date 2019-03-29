<?php

namespace Drupal\alshaya_acm_product_position;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;

/**
 * Class AlshayaPlpSortOptionsBase.
 */
class AlshayaPlpSortOptionsBase {

  /**
   * Config id.
   *
   * @see alshaya_acm_product_position.settings in alshaya_pb_transac.
   */
  const CONFIG_SORT_OPTIONS = 'alshaya_acm_product_position.settings';

  /**
   * Vocabulary of plp page.
   */
  const VOCABULARY_ID = 'acq_product_category';

  /**
   * Mapping for fields for options and label to get settings.
   */
  const SORT_OPTIONS_SETTINGS = [
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
   * AlshayaPlpSortOptionsService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->configSortOptions = $config_factory->get(self::CONFIG_SORT_OPTIONS);
    $this->routeMatch = $route_match;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
  }

  /**
   * Get taxonomy term from current route.
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   *   Return taxonomy term object when available else null.
   */
  protected function getTermForRoute() {
    $route_name = $this->routeMatch->getRouteName();

    if (!in_array($route_name, ['entity.taxonomy_term.canonical', 'rest.category_product_list.GET'])) {
      return NULL;
    }

    // If /taxonomy/term/tid page.
    if ($route_name == 'entity.taxonomy_term.canonical') {
      /* @var \Drupal\taxonomy\TermInterface $route_parameter_value */
      $term = $this->routeMatch->getParameter('taxonomy_term');
    }
    elseif ($route_name == 'rest.category_product_list.GET') {
      // In case of rest resource.
      $term_id = $this->routeMatch->getParameter('id');
      $term = $this->termStorage->load($term_id);
    }

    if (empty($term) || !$term instanceof Term) {
      return NULL;
    }

    if ($term->bundle() !== self::VOCABULARY_ID) {
      return NULL;
    }

    return $term;
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
  protected function getPlpSortConfigForTerm(TermInterface $taxonomy_term, $type = 'options') : ?array {
    $sorting_options = $taxonomy_term->get(self::SORT_OPTIONS_SETTINGS[$type]['type'])->getString();
    if ($sorting_options == 'inherit_site') {
      return NULL;
    }

    if ($sorting_options == 'override' && $sorting_options = $taxonomy_term->get(self::SORT_OPTIONS_SETTINGS[$type]['value'])->getString()) {
      return unserialize($sorting_options);
    }
    elseif ($sorting_options == 'inherit_category') {
      // Get size guide link from parent term.
      $parent_terms = $this->termStorage->loadParents($taxonomy_term->id());
      $term = reset($parent_terms);
      if (!$term instanceof Term) {
        return NULL;
      }
      return $this->getPlpSortConfigForTerm($term, $type);
    }
    return NULL;
  }

}
