<?php

namespace Drupal\alshaya_acm_product_position;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;
use Drupal\views\Views;
use Drupal\alshaya_custom\AlshayaDynamicConfigValueBase;

/**
 * Class AlshayaPlpSortOptionsService.
 */
class AlshayaPlpSortOptionsService {

  /**
   * Config id.
   *
   * @see alshaya_acm_product_position.settings in alshaya_pb_transac.
   */
  const CONFIG_SORT_OPTIONS = 'alshaya_acm_product_position.settings';

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
   * Get the labels for plp sorting options.
   */
  public function getSortOptionsLabels() {
    return array_filter(AlshayaDynamicConfigValueBase::schemaArrayToKeyValue(
      (array) $this->configSortOptions->get('sort_options_labels')
    ));
  }

  /**
   * Sort the given options.
   *
   * @param array $options
   *   Array of plp options to sort.
   *
   * @return array
   *   Sorted array with labels.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function sortGivenOptions(array $options): array {
    $config_values = $this->getCurrentPagePlpSortOptions();

    $config_sort_options = array_keys($config_values);
    // If there are at least any sort option enabled.
    if (!empty($config_sort_options)) {
      $new_sort_options = [];

      // Get the default sorting for options from views config.
      $views_storage = Views::getView('alshaya_product_list')->storage;
      $views_sort = $views_storage->getDisplay('block_1')['display_options']['sorts'];

      // Iterate over config sort options to prepare new sorted array for form
      // value option.
      foreach ($config_sort_options as $sort_options) {
        // Set default sort option ASC/DESC from the views config/sort order.
        $default_sort_order = $views_sort[$sort_options]['order'];
        $secondary_sort_order = $views_sort[$sort_options]['order'] == 'ASC' ? 'DESC' : 'ASC';
        if (isset($options[$sort_options . ' ' . $default_sort_order])) {
          $new_sort_options[$sort_options . ' ' . $default_sort_order] = $options[$sort_options . ' ' . $default_sort_order];
        }
        if (isset($options[$sort_options . ' ' . $secondary_sort_order])) {
          $new_sort_options[$sort_options . ' ' . $secondary_sort_order] = $options[$sort_options . ' ' . $secondary_sort_order];
        }
      }

      if (!empty($new_sort_options)) {
        return $new_sort_options;
      }
    }

    return $options;
  }

  /**
   * Get sort options for current page.
   *
   * @return array|null
   *   Return array of plp sort options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getCurrentPagePlpSortOptions() {
    $route_name = $this->routeMatch->getRouteName();
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
      return array_filter($this->configSortOptions->get('sort_options'));
    }
    return array_filter($this->getPlpSortOptionsForTerm($term));
  }

  /**
   * Get plp sort option setting from parent term of the given term.
   *
   * @param \Drupal\taxonomy\TermInterface $taxonomy_term
   *   The taxonomy term object.
   *
   * @return array
   *   Return array value.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getPlpSortOptionsForTerm(TermInterface $taxonomy_term): array {
    $sorting_options = $taxonomy_term->get('field_sorting_options')->getString();
    if ($sorting_options == 'override') {
      return unserialize($taxonomy_term->get('field_sorting_order')->getString());
    }
    elseif ($sorting_options == 'inherit_category') {
      // Get size guide link from parent term.
      $parent_terms = $this->termStorage->loadParents($taxonomy_term->id());
      $term = reset($parent_terms);
      if (!$term instanceof Term) {
        return $this->configSortOptions->get('sort_options');
      }
      return $this->getPlpSortOptionsForTerm($term);
    }
    else {
      return $this->configSortOptions->get('sort_options');
    }
  }

}
