import algoliasearch from 'algoliasearch/lite';

export const searchClient = algoliasearch(
  drupalSettings.algoliaSearch.application_id,
  drupalSettings.algoliaSearch.api_key,
);
export const algoliaSearchClient = {
  search(requests) {
    const searchRequest = requests;
    if (window.algoliaSearchActivityStarted || searchRequest[0].params.query.length > 0) {
      searchRequest[0].params.analyticsTags = drupalSettings.user.isCustomer
        ? ['customer']
        : ['notCustomer'];

      return searchClient.search(searchRequest);
    }

    return null;
  },
};
