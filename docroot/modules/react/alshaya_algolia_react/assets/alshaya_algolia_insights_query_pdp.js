/**
 * @file
 * This file add the data-insights-query-id on PDP.
 */
(function ($, Drupal) {

  /**
   * Helper function fetch Sku Algolia Insights Click Data.
   */
    Drupal.dataInsightsQuery= function (e) {
         var sku= $('article.data-insights-query-class').attr('data-sku');
         if (sku!== null) {
          Drupal.fetchSkuAlgoliaInsightsClickData(sku);
        }
    };
    Drupal.dataInsightsQuery();
})(jQuery, Drupal);
