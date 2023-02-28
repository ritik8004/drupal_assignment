/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal) {

  /**
   * Fetch Algolia Insight click data from local storage.
   */
  Drupal.fetchSkuAlgoliaInsightsClickData = function (sku) {
    try {
      if (Drupal.getItemFromLocalStorage('algolia_search_clicks') !== null) {
        var algoliaClicks = Drupal.getItemFromLocalStorage('algolia_search_clicks');
        if (algoliaClicks
          && algoliaClicks[sku] !== undefined
          && algoliaClicks[sku] !== null
          && typeof algoliaClicks[sku] !== 'string') {
          return {
            queryId: algoliaClicks[sku]['query-id'],
            objectId: algoliaClicks[sku]['object-id'],
            indexName: algoliaClicks[sku]['index-name'],
          };
        }
      }
    } catch (e) {
      console.error(e);
      return null;
    }
  };
})(jQuery, Drupal);
