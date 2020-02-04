/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal) {
  'use strict';

  // Copy queryID from local store to tag in html.
  // This is to ensure we have queryID even after we update URL in cases
  // where color split is enabled.
  var sku = $('.sku-base-form').closest('article[gtm-type="gtm-product-link"]').attr('gtm-main-sku');
  if (localStorage.getItem('algolia_search_clicks') !== null) {
    var algolia_clicks = JSON.parse(localStorage.getItem('algolia_search_clicks'));
    $('html').attr('data-algolia-query-id', algolia_clicks[sku]);
  }

  Drupal.behaviors.alshayaAlgoliaInsightsDetail = {
    attach: function (context) {
      $('.sku-base-form').once('alshayaAlgoliaInsightsDetail').on('product-add-to-cart-success', function () {
        // Do nothing if no query id to send.
        if ($('html').attr('data-algolia-query-id') === '') {
          return;
        }

        var addedProduct = $(this).closest('article[gtm-type="gtm-product-link"]');

        window.aa('convertedObjectIDsAfterSearch', {
          userToken: Drupal.getAlgoliaUserToken(),
          eventName: 'Add to cart',
          index: "...",
          queryID: $('html').attr('data-algolia-query-id'),
          objectIDs: [addedProduct.attr('data-insights-object-id')]
        });
      });
    }
  };

})(jQuery, Drupal);
