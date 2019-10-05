import algoliasearch from 'algoliasearch/lite';

const algoliaClient = algoliasearch(
  drupalSettings.algoliaSearch.application_id,
  drupalSettings.algoliaSearch.api_key
);

export const searchClient = {
  search(requests) {
    if (requests.every(({ indexName, params }) => indexName === drupalSettings.algoliaSearch.indexName && !params.query)) {
      return Promise.resolve({
        results: requests.map(() => ({
          hits: [],
          nbHits: 0,
          nbPages: 0,
          processingTimeMS: 0,
        })),
      });
    }
    return algoliaClient.search(requests);
  },
};
