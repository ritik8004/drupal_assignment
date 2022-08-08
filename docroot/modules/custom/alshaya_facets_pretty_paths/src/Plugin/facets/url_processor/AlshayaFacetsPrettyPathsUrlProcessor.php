<?php

namespace Drupal\alshaya_facets_pretty_paths\Plugin\facets\url_processor;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\facets\FacetInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets\UrlProcessor\UrlProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Pretty paths URL processor.
 *
 * @FacetsUrlProcessor(
 *   id = "alshaya_facets_pretty_paths",
 *   label = @Translation("Pretty paths"),
 *   description = @Translation("Pretty paths uses -- and - as separator, e.g.
 *   /brand/drupal/--color-blue"),
 * )
 */
class AlshayaFacetsPrettyPathsUrlProcessor extends UrlProcessorPluginBase {

  /**
   * Active filters array.
   *
   * @var array
   *   An array containing the active filters
   */
  protected $activeFilters = [];

  /**
   * The pretty path helper service.
   *
   * @var \Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper
   */
  protected $alshayaPrettyPathHelper;

  /**
   * Tha facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetsManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Request $request,
                              AlshayaFacetsPrettyPathsHelper $pretty_path_helper,
                              DefaultFacetManager $facets_manager,
                              EntityTypeManagerInterface $entityTypeManager,
                              LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request, $entityTypeManager);
    $this->alshayaPrettyPathHelper = $pretty_path_helper;
    $this->facetsManager = $facets_manager;
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $language_manager;
    $this->initializeActiveFilters($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')->getMasterRequest(),
      $container->get('alshaya_facets_pretty_paths.pretty_paths_helper'),
      $container->get('facets.manager'),
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildUrls(FacetInterface $facet, array $results) {

    // No results are found for this facet, so don't try to create urls.
    if (empty($results)) {
      return [];
    }

    // We use alias and label for English facet all the time.
    $facet = $this->getEnglishFacet($facet);

    $current_path = rtrim($this->request->getPathInfo(), '/');
    $filters_array = $this->alshayaPrettyPathHelper->getActiveFacetFilters($facet->getFacetSourceId());

    $active_results = [];
    foreach ($results as $key => $result) {
      if ($result->isActive()) {
        $active_results[$key] = $result;
      }
    }

    $filters_current_result = [];
    foreach ($filters_array as $filters) {
      $array = explode('-', $filters);
      $key = array_shift($array);

      foreach ($array as &$activeValue) {
        $activeValue = $this->alshayaPrettyPathHelper->decodeFacetUrlComponents(
          $facet->getFacetSourceId(),
          $key,
          $activeValue
        );
      }

      // 0 is a valid value so we use strlen.
      $filters_current_result[$key] = array_filter($array, 'strlen');
    }

    $filters_current_result = array_filter($filters_current_result);

    /** @var \Drupal\facets\Result\ResultInterface $result */
    foreach ($results as $result_key => &$result) {
      $filters_current_result_array = $filters_current_result;

      $filter_key = $facet->getUrlAlias();
      $raw_value = $result->getRawValue();

      // If the value is active, remove the filter string from the parameters.
      if (!empty($active_results[$result_key])) {
        $active_facet = [];
        foreach ($filters_current_result_array[$filter_key] as $value) {
          $active_facet[] = $value;
        }

        if (($active_key = array_search($raw_value, $active_facet)) !== FALSE) {
          unset($active_facet[$active_key]);
        }
        $filters_current_result_array[$filter_key] = $active_facet;
      }
      // If the value is not active, add the filter string.
      else {
        $filters_current_result_array[$filter_key][] = $raw_value;

        if ($facet->getUseHierarchy()) {
          // If hierarchy is active, unset parent trail and every child when
          // building the enable-link to ensure those are not enabled anymore.
          $parent_ids = $facet->getHierarchyInstance()
            ->getParentIds($raw_value);
          $child_ids = $facet->getHierarchyInstance()
            ->getNestedChildIds($raw_value);
          $parents_and_child_ids = array_merge($parent_ids, $child_ids);
          foreach ($parents_and_child_ids as $id) {
            unset($filters_current_result_array[array_search($id, $filters_current_result_array[$filter_key])]);
          }
        }
        // Exclude currently active results from the filter params if we are in
        // the show_only_one_result mode.
        if ($facet->getShowOnlyOneResult()) {
          foreach ($active_results as $result2) {
            unset($filters_current_result_array[array_search($result2->getRawValue(), $filters_current_result_array[$filter_key])]);
          }
        }
      }

      $filters_current_result_array = array_filter($filters_current_result_array);

      if (str_contains($current_path, "/--")) {
        $current_path = substr($current_path, 0, strpos($current_path, '/--'));
      }

      $filters_count = 0;
      if (count($filters_current_result_array)) {
        foreach ($filters_current_result_array as $key => $values) {
          $encoded = [];
          foreach ($values as $value) {
            // If sizegroup is enabled and user tries to load a page with only
            // one value in URL for sizegroup filter (for instance/--size-XL/)
            // we will ignore/remove that filter.
            if ($key == 'size'
              && !str_contains($value, SkuManager::SIZE_GROUP_SEPARATOR)
              && $this->alshayaPrettyPathHelper->isSizeGroupEnabled()) {
              continue;
            }
            $encoded[] = $this->alshayaPrettyPathHelper->encodeFacetUrlComponents($facet->getFacetSourceId(), $key, $value);
            $filters_count++;
          }
          $filters_current_result_array[$key] = $key . '-' . implode('-', $encoded);
        }

        $filters_current_result_string = implode('--', $filters_current_result_array);
        $current_path = rtrim($current_path, '/');

        $url = Url::fromUri('base:' . $current_path . '/--' . $filters_current_result_string . '/');
      }
      else {
        $url = Url::fromUri('base:' . $current_path . '/');
      }

      $attributes = [];

      // If more than 2 filters are selected, don't index.
      $attributes['rel'] = ($filters_count > Settings::get('nonindexable_plp_filter_count'))
        ? 'nofollow'
        : 'follow';

      // Getting the filter item value in English.
      // Setting attribute for the facet items.
      $filter_value_en = $this->alshayaPrettyPathHelper->encodeFacetUrlComponents(
        $facet->getFacetSourceId(),
        $facet->getUrlAlias(),
        $raw_value
      );
      $attributes['data-drupal-facet-item-label'] = $filter_value_en;
      $attributes['data-drupal-facet-label'] = $facet->label();

      $url->setOption('attributes', $attributes);
      $url->setOption('query', $this->getQueryParams());
      $result->setUrl($url);
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveItems(FacetInterface $facet) {
    // We use alias and label for English facet all the time.
    $facet_en = $this->getEnglishFacet($facet);

    // Get the filter key of the facet.
    if (isset($this->activeFilters[$facet_en->getUrlAlias()])) {
      foreach ($this->activeFilters[$facet_en->getUrlAlias()] as $value) {
        $decoded = $this->alshayaPrettyPathHelper->decodeFacetUrlComponents(
          $facet_en->getFacetSourceId(),
          $facet_en->getUrlAlias(),
          $value
        );

        // 0 is a valid value, we will have NULL if value not found so we
        // use isset().
        if (isset($decoded)) {
          $facet->setActiveItem(trim($decoded, '"'));
        }
      }
    }
  }

  /**
   * Initialize the active filters.
   *
   * Get all the filters that are active. This method only get's all the
   * filters but doesn't assign them to facets. In the processFacet method the
   * active values for a specific facet are added to the facet.
   */
  protected function initializeActiveFilters($configuration) {
    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = $configuration['facet'];

    // We use alias and label for English facet all the time.
    $facet = $this->getEnglishFacet($facet);

    $parts = $this->alshayaPrettyPathHelper->getActiveFacetFilters($facet->getFacetSourceId());
    foreach ($parts as $part) {
      $new_parts = explode('-', $part);

      // First element is always the facet key.
      $key = array_shift($new_parts);

      if ($facet->getUrlAlias() != $key) {
        continue;
      }

      $this->activeFilters[$key] = $new_parts;
    }
  }

  /**
   * Wrapper function to get query params for facet url.
   *
   * It removes page and returns rest.
   *
   * @return array
   *   Query params.
   */
  protected function getQueryParams() {
    static $query;

    if (empty($query)) {
      $query = $this->request->query->all();
      if (isset($query['page'])) {
        unset($query['page']);
      }
    }

    return $query;
  }

  /**
   * Get facet label in English.
   *
   * Load original facet without any overrides on non-English pages so that
   * values such as facet label can be loaded in English which can then be
   * sent to GTM.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   Facet.
   *
   * @return \Drupal\facets\FacetInterface
   *   Facet label in English.
   */
  protected function getEnglishFacet(FacetInterface $facet) {
    static $facets = [];

    if (isset($facets[$facet->id()])) {
      return $facets[$facet->id()];
    }

    $storage = $this->entityTypeManager->getStorage($facet->getEntityTypeId());
    $facetEn = $storage->loadOverrideFree($facet->id());

    $facets[$facet->id()] = $facetEn;
    return $facets[$facet->id()];
  }

}
