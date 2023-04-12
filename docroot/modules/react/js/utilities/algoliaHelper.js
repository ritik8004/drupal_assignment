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
  const { indexName } = drupalSettings.algoliaSearch[pageType];
  const index = searchClient.initIndex(indexName);
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

  const indexSettings = await getIndexSettings(pageType);
  const { attributesForFaceting } = indexSettings;
  if (hasValue(attributesForFaceting)) {
    Drupal.addItemInLocalStorage(`${pageType}-facets`, attributesForFaceting, 86400);
    facets = attributesForFaceting;
  }
  return facets;
};
