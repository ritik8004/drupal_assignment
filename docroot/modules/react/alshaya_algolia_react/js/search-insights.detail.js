/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal) {

  Drupal.behaviors.alshayaAlgoliaInsightsDetail = {
    attach: function (context) {
      $('.sku-base-form').once('alshayaAlgoliaInsightsDetail').on('product-add-to-cart-success', function () {
        var sku = $(this).attr('data-sku');
        var queryId, objectId = null;
        var indexName = '...';

        try {
          if (Drupal.getItemFromLocalStorage('algolia_search_clicks') !== null) {
            var algolia_clicks = Drupal.getItemFromLocalStorage('algolia_search_clicks');
            if (algolia_clicks
              && algolia_clicks[sku] !== undefined
              && algolia_clicks[sku] !== null
              && typeof algolia_clicks[sku] !== 'string') {
              queryId = algolia_clicks[sku]['query-id'];
              objectId = algolia_clicks[sku]['object-id'];
              indexName = algolia_clicks[sku]['index-name'];
            }
          }
        }
        catch (e) {
          console.error(e);
          return;
        }

        if (!queryId || !objectId) {
          return;
        }

        Drupal.pushAlshayaAlgoliaInsightsAddToCart(queryId, objectId, indexName);
      });
    }
  };

})(jQuery, Drupal);
