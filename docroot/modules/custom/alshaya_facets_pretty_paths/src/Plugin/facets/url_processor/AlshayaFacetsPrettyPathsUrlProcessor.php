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

    $facet_weights = &drupal_static('facetPrettyWeights', []);

    if (empty($facet_weights)) {
      // Get all facets of the given source.
      $block_facets = \Drupal::service('facets.manager')->getFacetsByFacetSourceId($facet->getFacetSourceId());
      $block_ids = [];
      if (!empty($block_facets)) {
        foreach ($block_facets as $block_facet) {
          $block_ids[$block_facet->getUrlAlias()] = str_replace('_', '', $block_facet->id());
        }

        if (!empty($block_ids)) {
          /* @var \Drupal\block\Entity\Block[] $block*/
          $blocks_list = \Drupal::entityTypeManager()->getStorage('block')->loadMultiple($block_ids);

          // Sort the blocks.
          uasort($block_ids, function ($a, $b) use ($blocks_list) {
            $a_weight = $blocks_list[$a]->getWeight();
            $b_weight = $blocks_list[$b]->getWeight();
            if ($a_weight == $b_weight) {
              return 0;
            }
            return ($a_weight < $b_weight) ? -1 : 1;
          });
        }
      }
      $facet_weights = array_keys($block_ids);
    }

    /** @var \Drupal\facets\Result\ResultInterface $result */
    foreach ($results as &$result) {
      $filters_current_result_array = [];
      foreach ($filters_array as $filters) {
        $array = explode('-', $filters);
        $key = array_shift($array);
        $filters_current_result_array[$key] = $array;
      }

      $filter_key = $facet->getUrlAlias();
      $raw_value = $result->getRawValue();
      // If the value is active, remove the filter string from the parameters.
      if ($result->isActive()) {
        $active_facet = [];

        foreach ($filters_current_result_array[$filter_key] as $value) {
          $active_facet[] = $this->alshayaPrettyPathHelper->decodeFacetUrlComponents($facet->getUrlAlias(), $value);
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
          foreach ($results as $result2) {
            if ($result2->isActive()) {
              unset($filters_current_result_array[array_search($result2->getRawValue(), $filters_current_result_array[$filter_key])]);
            }
          }
        }
      }

      $filters_current_result_array = array_replace(array_intersect_key(array_flip($facet_weights), $filters_current_result_array), $filters_current_result_array);
      $filters_current_result_array = array_filter($filters_current_result_array);

      if (strpos($current_path, "/--") !== FALSE) {
        $current_path = substr($current_path, 0, strpos($current_path, '/--'));
      }

      $filters_count = 0;
      if (count($filters_current_result_array)) {
        foreach ($filters_current_result_array as $key => $values) {
          $encoded = [];
          foreach ($values as $value) {
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

      if ($filters_count > 2) {
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
        $facet->setActiveItem(trim($this->alshayaPrettyPathHelper->decodeFacetUrlComponents($facet->getUrlAlias(), $value), '"'));
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

      // First element is always the facet key.
      $key = array_shift($new_parts);

      /** @var \Drupal\facets\FacetInterface $facet */
      $facet = $configuration['facet'];
      if ($facet->getUrlAlias() != $key) {
        continue;
      }

      $this->activeFilters[$key] = $new_parts;
    }
  }

}
