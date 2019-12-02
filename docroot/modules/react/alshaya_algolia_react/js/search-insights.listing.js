/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshayaAlgoliaInsightsListing = {
    attach: function (context) {
      $('#alshaya-algolia-search').once('alshayaAlgoliaInsights').on('click', '[data-insights-query-id] .product-selected-url', function (event) {
        var hit = $(this).closest('[data-insights-query-id]');

        if (drupalSettings.userDetails === undefined || drupalSettings.userDetails.userID === undefined || !(drupalSettings.userDetails.userID)) {
          var userToken = $.cookie('_ALGOLIA');
        }
        else {
          var userToken = drupalSettings.userDetails.userID;
        }

        window.aa('clickedObjectIDsAfterSearch', {
          userToken: userToken,
          eventName: 'Visit Detail Page',
          index: "...",
          queryID: hit.attr('data-insights-query-id'),
          objectIDs: [hit.attr('data-insights-object-id')],
          positions: [parseInt(hit.attr('data-insights-position'))],
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
