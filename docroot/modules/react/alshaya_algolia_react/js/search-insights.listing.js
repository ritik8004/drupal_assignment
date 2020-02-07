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
        var algolia_clicks = JSON.parse(localStorage.getItem('algolia_search_clicks'));
        if (algolia_clicks === null) {
          algolia_clicks = {};
        }
        algolia_clicks[hit.attr('gtm-main-sku')] = hit.attr('data-insights-query-id');
        localStorage.setItem('algolia_search_clicks', JSON.stringify(algolia_clicks));

        window.aa('clickedObjectIDsAfterSearch', {
          userToken: Drupal.getAlgoliaUserToken(),
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
