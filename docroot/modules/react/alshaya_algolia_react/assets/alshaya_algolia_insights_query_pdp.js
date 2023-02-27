/**
 * @file
 * This file add the data-insights-query-id on PDP for V2.
 */
(function ($, Drupal) {

  /**
   * Helper dataInsightsQuery add the data-insights-query-id on PDP for V2.
   */
    Drupal.dataInsightsQuery = function () {
         var sku = $('body').find('.data-insights-query-class').attr('data-sku');
         if (Drupal.hasValue(sku)) {
          var insightsClickData = Drupal.fetchSkuAlgoliaInsightsClickData(sku);
          if (insightsClickData && Drupal.hasValue(insightsClickData.queryId)) {
            $('body').find('.data-insights-query-class').attr('data-insights-query-id', insightsClickData.queryId);
          }
        }
    };
    Drupal.dataInsightsQuery();

})(jQuery, Drupal);
