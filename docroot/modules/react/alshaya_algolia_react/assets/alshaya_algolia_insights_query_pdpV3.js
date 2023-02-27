/**
 * This addListener add the data-insights-query-id on PDP for V3.
 */
(function main($, RcsEventManager) {

    /**
     * This event add the data-insights-query-id on PDP for V3.
     */
      RcsEventManager.addListener('alshayaPageEntityLoaded', function (e) {
        var mainProduct = e.detail.entity;
        if (Drupal.hasValue(mainProduct.sku)) {
          const insightsClickDataV3 = Drupal.fetchSkuAlgoliaInsightsClickData(mainProduct.sku);
          if (insightsClickDataV3 && Drupal.hasValue(insightsClickDataV3.queryId)) {
             $('body').find('.data-insights-query-class').attr('data-insights-query-id', insightsClickDataV3.queryId);
           }
        }
      });
  
    })(jQuery, RcsEventManager);
