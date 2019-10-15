/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaAlgoliaInsightsDetail = {
    attach: function (context) {
      $('.sku-base-form').once('alshayaAlgoliaInsightsDetail').on('product-add-to-cart-success', function () {
        // Do nothing if no query id to send.
        if (Drupal.getQueryVariable('queryID') === '') {
          return;
        }

        var addedProduct = $(this).closest('article[gtm-type="gtm-product-link"]');

        window.aa('clickedObjectIDsAfterSearch', {
          eventName: 'Add to cart',
          index: "...",
          queryID: Drupal.getQueryVariable('queryID'),
          objectIDs: [addedProduct.attr('data-insights-object-id')]
        });
      });
    }
  };

})(jQuery, Drupal);
