/**
 * @file
 * This file add the data-insights-query-id on PDP.
 */
(function ($, Drupal) {

  /**
   * Helper function fetch Sku Algolia Insights Click Data.
   */
    Drupal.dataInsightsQuery= function (e) {
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
    Drupal.dataInsightsQuery();
})(jQuery, Drupal);
