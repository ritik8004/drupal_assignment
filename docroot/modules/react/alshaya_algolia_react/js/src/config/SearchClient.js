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
    // Remove tagFilters from all search queries if empty.
    searchRequest.forEach((request) => {
      delete request.params.tagFilters;
      // Remove maxValuesPerFacet from all search quesries.
      if ('maxValuesPerFacet' in request.params) {
        delete request.params.maxValuesPerFacet;
      }
    });

    let referrerData = Drupal.getItemFromLocalStorage('referrerData');
    const isSearchActivated = Drupal.getItemFromLocalStorage('isSearchActivated');

    // Check if Search is activated on any page.
    if (isSearchActivated) {
      if (referrerData !== null) {
        if (referrerData.list && referrerData.list !== 'Search Results Page') {
          // Store the pageType temporarily and set SRP as pagetype.
          referrerData.previousList = referrerData.list;
          referrerData.list = 'Search Results Page';
          referrerData.path = window.location.href;
          Drupal.addItemInLocalStorage('referrerData', referrerData);
        }
      } else {
        // If referrerData is not in localStorage,
        // Store referrerData in localStorage.
        referrerData = {
          pageType: 'Search Results Page',
          path: window.location.href,
          list: 'Search Results Page',
          previousList: '',
        };
        Drupal.addItemInLocalStorage('referrerData', referrerData);
      }
    } else if (referrerData !== null && referrerData.previousList !== '') {
      referrerData.list = referrerData.previousList;
      referrerData.previousList = '';
      Drupal.addItemInLocalStorage('referrerData', referrerData);
    }

    if (window.algoliaSearchActivityStarted || searchRequest[0].params.query.length > 0) {
      searchRequest[0].params.analyticsTags = drupalSettings.user.isCustomer
        ? ['customer']
        : ['notCustomer'];

      return searchClient.search(searchRequest);
    }

    return null;
  },
};
