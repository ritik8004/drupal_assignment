import algoliasearch from 'algoliasearch/lite';

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
