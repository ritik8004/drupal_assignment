<?php

namespace Drupal\alshaya_search\Plugin\facets\url_processor;

use Drupal\Core\Url;
use Drupal\facets\Plugin\facets\url_processor\QueryString;
use Drupal\facets\FacetInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * AlshayaQuery string URL processor.
 *
 * @FacetsUrlProcessor(
 *   id = "alshaya_query_string",
 *   label = @Translation("Alshaya query string"),
 *   description = @Translation("Extending the facet's query string url processor plugin")
 * )
 */
class AlshayaQueryString extends QueryString {

  /**
   * {@inheritdoc}
   */
  public function buildUrls(FacetInterface $facet, array $results) {
    // For other than category filter, we use default query_string url
    // processor.
    if (!$facet->getUseHierarchy()) {
      return parent::buildUrls($facet, $results);
    }

    // No results are found for this facet, so don't try to create urls.
    if (empty($results)) {
      return [];
    }

    // First get the current list of get parameters.
    $get_params = $this->request->query;

    // When adding/removing a filter the number of pages may have changed,
    // possibly resulting in an invalid page parameter.
    if ($get_params->has('page')) {
      $current_page = $get_params->get('page');
      $get_params->remove('page');
    }

    // Set the url alias from the the facet object.
    $this->urlAlias = $facet->getUrlAlias();

    $request = $this->request;
    if ($facet->getFacetSource()->getPath()) {
      $request = Request::create($facet->getFacetSource()->getPath());
    }

    /** @var \Drupal\facets\Result\ResultInterface[] $results */
    foreach ($results as &$result) {
      // Reset the URL for each result.
      $url = Url::createFromRequest($request);
      $url->setOption('attributes', ['rel' => 'nofollow']);

      // Sets the url for children.
      if ($children = $result->getChildren()) {
        $this->buildUrls($facet, $children);
      }

      $filter_string = $this->urlAlias . $this->getSeparator() . $result->getRawValue();
      $result_get_params = clone $get_params;

      $filter_params = $result_get_params->get($this->filterKey, [], TRUE);
      // If the value is active, remove the filter string from the parameters.
      if ($result->isActive()) {
        foreach ($filter_params as $key => $filter_param) {
          if ($filter_param == $filter_string) {
            unset($filter_params[$key]);
          }
        }
        if ($facet->getEnableParentWhenChildGetsDisabled() && $facet->getUseHierarchy()) {
          // Enable parent id again if exists.
          $parent_ids = $facet->getHierarchyInstance()->getParentIds($result->getRawValue());
          if (isset($parent_ids[0]) && $parent_ids[0]) {
            $filter_params[] = $this->urlAlias . $this->getSeparator() . $parent_ids[0];
          }
        }

      }
      // If the value is not active, add the filter string.
      else {
        $filter_params[] = $filter_string;

        if ($facet->getUseHierarchy()) {
          // If hierarchy is active, unset parent trail and every child when
          // building the enable-link to ensure those are not enabled anymore.
          $parent_ids = $facet->getHierarchyInstance()->getParentIds($result->getRawValue());
          $child_ids = $facet->getHierarchyInstance()->getNestedChildIds($result->getRawValue());
          $parents_and_child_ids = array_merge($parent_ids, $child_ids);
          foreach ($parents_and_child_ids as $id) {
            $filter_params = array_diff($filter_params, [$this->urlAlias . $this->getSeparator() . $id]);
          }

          // If we processing the URL for an L1 item, remove all category
          // filters. Clicking on an L1 item when the active filter is something
          // else, should return back to default state.
          $facet_source_id = $facet->getFacetSourceId();
          // We doing this as for the PLP page, we not show the L1
          // (current active) item.
          $facet_count = $facet_source_id == 'search_api:views_block__alshaya_product_list__block_1' ? 1 : 0;
          if ((count($parent_ids) === $facet_count) &&
            (isset($this->activeFilters[$this->urlAlias])) &&
            (count($this->activeFilters[$this->urlAlias]) > 0)) {
            foreach ($filter_params as $key => $param) {
              if (strpos($param, $this->urlAlias) === 0) {
                unset($filter_params[$key]);
              }
            }
          }
        }

        // Exclude currently active results from the filter params if we are in
        // the show_only_one_result mode.
        if ($facet->getShowOnlyOneResult()) {
          foreach ($results as $result2) {
            if ($result2->isActive()) {
              $active_filter_string = $this->urlAlias . $this->getSeparator() . $result2->getRawValue();
              foreach ($filter_params as $key2 => $filter_param2) {
                if ($filter_param2 == $active_filter_string) {
                  unset($filter_params[$key2]);
                }
              }
            }
          }
        }
      }

      $result_get_params->set($this->filterKey, array_values($filter_params));
      // Grab any route params from the original request.
      $routeParameters = Url::createFromRequest($this->request)
        ->getRouteParameters();
      if (!empty($routeParameters)) {
        $url->setRouteParameters($routeParameters);
      }

      $new_url = clone $url;
      if ($result_get_params->all() !== [$this->filterKey => []]) {
        $new_url->setOption('query', $result_get_params->all());
      }

      $result->setUrl($new_url);
    }

    // Restore page parameter again. See https://www.drupal.org/node/2726455.
    if (isset($current_page)) {
      $get_params->set('page', $current_page);
    }

    return $results;
  }

}
