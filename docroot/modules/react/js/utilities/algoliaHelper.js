import algoliasearch from 'algoliasearch';
import { hasValue } from './conditionsUtility';

// Adding _useRequestCache parameter to avoid duplicate requests on Facet filters and sort orders.
export const searchClient = algoliasearch(
  drupalSettings.algoliaSearch.application_id,
  drupalSettings.algoliaSearch.api_key, {
    _useRequestCache: true,
  },
);
export const algoliaSearchClient = {
  search(requests) {
    const searchRequest = requests;
    searchRequest.forEach((request) => {
      // Remove tagFilters from all search queries.
      if ('tagFilters' in request.params) {
        delete request.params.tagFilters;
      }
      // Remove maxValuesPerFacet from all search quesries.
      if ('maxValuesPerFacet' in request.params) {
        delete request.params.maxValuesPerFacet;
      }
    });
    return searchClient.search(searchRequest);
  },
};

/**
 * Get algolia index settings.
 *
 * @param {string} pageType
 *   Page type eg: search, listing.
 *
 * @returns {Promise}
 *   Settings object.
 */
export const getIndexSettings = (pageType) => {
  // Get index name from drupal settings.
  const { indexName } = drupalSettings.algoliaSearch[pageType];
  // Initialize index.
  const index = searchClient.initIndex(indexName);

  // Get index settings.
  return index.getSettings().then((response) => response);
};

/**
 * Facets from index settings.
 *
 * @param {string} pageType
 *   Page type eg: search, listing.
 *
 * @returns {Promise<Object|string>}
 *   Promise or array for facets from settings.
 */
export const getFacetListFromAlgolia = async (pageType) => {
  let facets = Drupal.getItemFromLocalStorage(`${pageType}-facets`);
  if (hasValue(facets)) {
    return facets;
  }
  facets = [];

  // Get facet list.
  const indexSettings = await getIndexSettings(pageType);
  const { attributesForFaceting } = indexSettings;
  if (hasValue(attributesForFaceting)) {
    // Store facets list in local storage with an expiry timestamp for 1hr.
    Drupal.addItemInLocalStorage(
      `${pageType}-facets`,
      attributesForFaceting,
      (hasValue(drupalSettings.algoliasearch)
        && hasValue(drupalSettings.algoliasearch.facetListExpiry)
        ? parseInt(drupalSettings.algoliasearch.facetListExpiry, 10)
        : 3600),
    );
    facets = attributesForFaceting;
  }
  return facets;
};
