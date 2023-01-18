/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

(function ($, Drupal) {

  Drupal.behaviors.alshayaAlgoliaInsightsListing = {
    attach: function (context) {
      /**
       * Trigger Algolia Insights event for viewing product details.
       */
      $('#alshaya-algolia-search, #alshaya-algolia-plp').once('alshayaAlgoliaInsights').on(
        'click',
        '[data-insights-query-id] .product-selected-url, [data-insights-query-id] .is-not-buyable, [data-insights-query-id] .addtobag-button, [data-insights-query-id] .addtobag-config-button',
        function (event) {
          // Do nothing for buttons inside our markup, for example in slick-dots.
          // Do nothing if user trying to use cmd/ctrl + click OR
          // cmd/ctrl + shift + click.
          if ((event.target.tagName.toLowerCase() === 'button' && event.currentTarget.className.includes('product-selected-url'))
            || event.metaKey
            || event.shiftKey
            || event.ctrlKey
          ) {
            return;
          }

          var hit = $(this).closest('[data-insights-query-id]');
          var algolia_clicks = Drupal.getItemFromLocalStorage('algolia_search_clicks');
          if (algolia_clicks === null) {
            algolia_clicks = {};
          }
          algolia_clicks[hit.attr('gtm-main-sku')] = {
            'query-id': hit.attr('data-insights-query-id'),
            'object-id': hit.attr('data-insights-object-id'),
            'index-name': hit.attr('data-insights-index'),
          };
          Drupal.addItemInLocalStorage('algolia_search_clicks', algolia_clicks);

          window.aa('clickedObjectIDsAfterSearch', {
            userToken: Drupal.getAlgoliaUserToken(),
            eventName: 'Visit Detail Page',
            index: hit.attr('data-insights-index'),
            queryID: hit.attr('data-insights-query-id'),
            objectIDs: [hit.attr('data-insights-object-id')],
            positions: [parseInt(hit.attr('data-insights-position'))],
          });
        });
    }
  };
})(jQuery, Drupal);
