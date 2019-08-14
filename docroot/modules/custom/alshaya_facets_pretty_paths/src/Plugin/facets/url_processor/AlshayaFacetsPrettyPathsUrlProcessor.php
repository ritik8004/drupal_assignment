<?php

namespace Drupal\alshaya_facets_pretty_paths\Plugin\facets\url_processor;

use Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper;
use Drupal\Core\Url;
use Drupal\facets\FacetInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets\UrlProcessor\UrlProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request, AlshayaFacetsPrettyPathsHelper $pretty_path_helper, DefaultFacetManager $facets_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request);
    $this->alshayaPrettyPathHelper = $pretty_path_helper;
    $this->facetsManager = $facets_manager;
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
      $container->get('facets.manager')
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

    $current_path = rtrim($this->request->getPathInfo(), '/');

    $filters_array = $this->alshayaPrettyPathHelper->getActiveFacetFilters();

    static $facet_weights;

    if (!isset($facet_weights)) {
      $facets = $this->facetsManager->getEnabledFacets();
      $facet_weights = [];
      foreach ($facets as $facet_selected) {
        $facet_weights[] = $facet_selected->getUrlAlias();
      }
    }

    /** @var \Drupal\facets\Result\ResultInterface $result */
    foreach ($results as &$result) {
      $filters_current_result_array = [];
      foreach ($filters_array as $filters) {
        $array = explode('-', $filters);
        $key = $array[0];
        array_shift($array);
        $filters_current_result_array[$key] = $array;
      }

      $raw_value = $result->getRawValue();
      $filter_key = $facet->getUrlAlias();
      // If the value is active, remove the filter string from the parameters.
      if ($result->isActive()) {
        $active_facet = array_map([
          '\Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper',
          'decodeFacetUrlComponents',
        ], $filters_current_result_array[$filter_key]);
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
          foreach ($results as $result2) {
            if ($result2->isActive()) {
              unset($filters_current_result_array[array_search($result2->getRawValue(), $filters_current_result_array[$filter_key])]);
            }
          }
        }
      }

      $filters_current_result_array = array_replace(array_intersect_key(array_flip($facet_weights), $filters_current_result_array), $filters_current_result_array);
      $filters_current_result_string = array_filter($filters_current_result_array);
      array_walk($filters_current_result_string, function (&$el, $key) {
        $el = $key . '-' . implode('-', array_map([
          '\Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper',
          'encodeFacetUrlComponents',
        ], $el));
      });
      $filters_current_result_string = implode('--', $filters_current_result_string);
      $path = $facet->getFacetSource()->getPath();
      if (strpos($current_path, "/--") !== FALSE) {
        $path = substr($path, 0, strpos($path, '/--'));
      }
      $path = rtrim($path, '/');

      $url = Url::fromUri('base:' . $path . '/--' . $filters_current_result_string);

      if (count($filters_current_result_array, COUNT_RECURSIVE) - count($filters_current_result_array) > 2) {
        $url->setOption('attributes', ['rel' => 'nofollow noindex ']);
      }
      else {
        $url->setOption('attributes', ['rel' => 'follow index']);
      }

      // First get the current list of get parameters.
      $get_params = $this->request->query;
      // When adding/removing a filter the number of pages may have changed,
      // possibly resulting in an invalid page parameter.
      if ($get_params->has('page')) {
        $current_page = $get_params->get('page');
        $get_params->remove('page');
      }
      $url->setOption('query', $get_params->all());
      $result->setUrl($url);
      // Restore page parameter again. See https://www.drupal.org/node/2726455.
      if (isset($current_page)) {
        $get_params->set('page', $current_page);
      }
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveItems(FacetInterface $facet) {
    // Get the filter key of the facet.
    if (isset($this->activeFilters[$facet->getUrlAlias()])) {

      foreach ($this->activeFilters[$facet->getUrlAlias()] as $value) {
        $facet->setActiveItem(trim($value, '"'));
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
    $parts = $this->alshayaPrettyPathHelper->getActiveFacetFilters();
    foreach ($parts as $part) {
      $new_parts = explode('-', $part);
      $key = $new_parts[0];
      array_shift($new_parts);
      $new_parts = array_map([
        '\Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper',
        'decodeFacetUrlComponents',
      ], $new_parts);
      $this->activeFilters[$key] = $new_parts;
    }
  }

}
