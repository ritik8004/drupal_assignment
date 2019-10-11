/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshayaAlgoliaInsights = {
    attach: function (context) {
      $('#alshaya-algolia-search').once('alshayaAlgoliaInsights').on('click', '[data-insights-query-id] .product-selected-url', function (event) {
        event.preventDefault();
        insights('clickedObjectIDsAfterSearch', {
          eventName: 'Product View'
        })
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
