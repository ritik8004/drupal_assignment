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
          var dataInsightsQueryId = algoliaClicks[sku]['query-id'];
          $('article.data-insights-query-class').attr('data-insights-query-id',dataInsightsQueryId);
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

  /**
   * Trigger Algolia Insight add to cart event from PDP.
   */
  Drupal.behaviors.alshayaAlgoliaInsightsDetail = {
    attach: function (context) {
      $('.sku-base-form').once('alshayaAlgoliaInsightsDetail').on('product-add-to-cart-success', function () {
        var sku = $(this).attr('data-sku');
        var insightsClickData = Drupal.fetchSkuAlgoliaInsightsClickData(sku);

        if (Drupal.hasValue(insightsClickData)
          && insightsClickData.queryId
          && insightsClickData.objectId) {
          Drupal.pushAlshayaAlgoliaInsightsAddToCart(
            insightsClickData.queryId,
            insightsClickData.objectId,
            insightsClickData.indexName
          );
        }
      });
    }
  };

})(jQuery, Drupal);
