/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal) {
  'use strict';

  // Copy queryID from query string to tag in html.
  // This is to ensure we have queryID even after we update URL in cases
  // where color split is enabled.
  $('html').attr('data-algolia-query-id', Drupal.getQueryVariable('queryID'));

  Drupal.behaviors.alshayaAlgoliaInsightsDetail = {
    attach: function (context) {
      $('.sku-base-form').once('alshayaAlgoliaInsightsDetail').on('product-add-to-cart-success', function () {
        // Do nothing if no query id to send.
        if ($('html').attr('data-algolia-query-id') === '') {
          return;
        }

        var addedProduct = $(this).closest('article[gtm-type="gtm-product-link"]');

        if (drupalSettings.userDetails === undefined || drupalSettings.userDetails.userID === undefined || !(drupalSettings.userDetails.userID)) {
          var userToken = $.cookie('_ALGOLIA');
        }
        else {
          var userToken = drupalSettings.userDetails.userID;
        }

        window.aa('convertedObjectIDsAfterSearch', {
          userToken: userToken,
          eventName: 'Add to cart',
          index: "...",
          queryID: $('html').attr('data-algolia-query-id'),
          objectIDs: [addedProduct.attr('data-insights-object-id')]
        });
      });
    }
  };

})(jQuery, Drupal);
