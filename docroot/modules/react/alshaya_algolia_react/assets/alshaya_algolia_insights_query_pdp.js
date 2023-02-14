/**
 * @file
 * This file add the data-insights-query-id on PDP.
 */
(function ($, Drupal, drupalSettings) {

    /**
   * Helper function fetch Sku Algolia Insights Click Data.
   *
   * @param customerType
   */
    Drupal.fetchSkuAlgoliaInsightsClickData = function (e) {
        if (Drupal.getItemFromLocalStorage('algolia_search_clicks') !== null) {
          var algoliaClicks = Drupal.getItemFromLocalStorage('algolia_search_clicks');
          var sku= $('article.data-insights-query-class').attr('data-sku');

          if (algoliaClicks
            && algoliaClicks[sku] !== undefined
            && algoliaClicks[sku] !== null
            && typeof algoliaClicks[sku] !== 'string') {
            var dataInsightsQueryId = algoliaClicks[sku]['query-id'];
            $('article.data-insights-query-class').attr('data-insights-query-id',dataInsightsQueryId);
        }
      }
    };
    Drupal.fetchSkuAlgoliaInsightsClickData();
})(jQuery, Drupal, drupalSettings);
