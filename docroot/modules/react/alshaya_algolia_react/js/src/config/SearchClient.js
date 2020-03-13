import algoliasearch from 'algoliasearch/lite';

export const searchClient = algoliasearch(
  drupalSettings.algoliaSearch.application_id,
  drupalSettings.algoliaSearch.api_key
);
export const algoliaSearchClient = {
  search(requests) {

    if (window.algoliaSearchActivityStarted || requests[0].params.query.length > 0) {
      requests[0].params['analyticsTags'] = drupalSettings.user.isCustomer === false
        ? ['notCustomer']
        : ['customer'];

      return searchClient.search(requests);
    }

    return null;
  }
};
