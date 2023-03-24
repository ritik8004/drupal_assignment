import algoliasearch from 'algoliasearch/lite';

export const searchClient = algoliasearch(
  drupalSettings.algoliaSearch.application_id,
  drupalSettings.algoliaSearch.api_key,
);
export const algoliaSearchClient = {
  search(requests) {
    const searchRequest = requests;
    // removing tagFilters for PLP pages and whishlist Pages
    if ('tagFilters' in searchRequest[0].params) {
      delete searchRequest[0].params.tagFilters;
    }
    // Remove maxValuesPerFacet from all search quesries for PLP and whislist.
    if ('maxValuesPerFacet' in searchRequest[0].params) {
      delete searchRequest[0].params.maxValuesPerFacet;
    }
    return searchClient.search(searchRequest);
  },
};
