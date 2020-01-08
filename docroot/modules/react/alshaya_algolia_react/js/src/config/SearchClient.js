import algoliasearch from 'algoliasearch/lite';

export const searchClient = algoliasearch(
  drupalSettings.algoliaSearch.application_id,
  drupalSettings.algoliaSearch.api_key
);
