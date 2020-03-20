/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaAlgoliaInsightsDetail = {
    attach: function (context) {
      $('.sku-base-form').once('alshayaAlgoliaInsightsDetail').on('product-add-to-cart-success', function () {
        var sku = $(this).attr('data-sku');
        var queryId = null;

        try {
          if (localStorage.getItem('algolia_search_clicks') !== null) {
            var algolia_clicks = JSON.parse(localStorage.getItem('algolia_search_clicks'));
            if (algolia_clicks && algolia_clicks[sku] !== undefined && algolia_clicks[sku] !== null) {
              queryId = algolia_clicks[sku];
            }
          }
        }
        catch (e) {
          console.error(e);
          return;
        }

        if (!queryId) {
          return;
        }

        var addedProduct = $(this).closest('article[gtm-type="gtm-product-link"]');

        try {
          window.aa('convertedObjectIDsAfterSearch', {
            userToken: Drupal.getAlgoliaUserToken(),
            eventName: 'Add to cart',
            index: "...",
            queryID: $('html').attr('data-algolia-query-id'),
            objectIDs: [addedProduct.attr('data-insights-object-id')]
          });
        }
        catch (e) {
          console.error(e);
        }
      });
    }
  };

})(jQuery, Drupal);
