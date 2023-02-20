/**
 * @file
 * This file add the data-insights-query-id on PDP.
 */
(function ($, Drupal) {

  /**
   * Helper function fetch Sku Algolia Insights Click Data.
   */
    Drupal.dataInsightsQuery= function () {
         var sku = $('article.data-insights-query-class').attr('data-sku');
         if (Drupal.hasValue(sku)) {
          var insightsClickData = Drupal.fetchSkuAlgoliaInsightsClickData(sku);
          if (insightsClickData && Drupal.hasValue(insightsClickData.queryId)) {
            $('article.data-insights-query-class').attr('data-insights-query-id', insightsClickData.queryId);
          }
        }
    };
    Drupal.dataInsightsQuery();
})(jQuery, Drupal);
