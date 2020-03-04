/**
 * @file
 * JS code to integrate with GTM for Algolia.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  var searchQuery = [];
  var initNoOfResults = null;

  // Bind for Algolia Search page. No impact if Algolia search not enabled
  // as selector won't be available.
  $(document).once('seoGoogleTagManager').on('search-results-updated', '#alshaya-algolia-search', function (event, noOfResult) {
    // Avoid triggering again for each page.
    var currentsearch = $('#alshaya-algolia-autocomplete input[name="search"]').val().trim();
    if (_.indexOf(searchQuery, currentsearch) < 0 && initNoOfResults !== noOfResult) {
      // Store all search queries in a temp array, so we don't trigger
      // event twice for the same keyword, while user repeats the search query
      // intermittently.
      searchQuery.push(currentsearch);

      dataLayer.push({
        event: 'eventTracker',
        eventCategory: 'Internal Site Search',
        eventAction: noOfResult === 0 ? '404 Results' : 'Successful Search',
        eventLabel: currentsearch,
        eventValue: noOfResult,
        nonInteraction: noOfResult === 0 ? noOfResult : 1,
      });
    }

    // Send impression for each product added on page (page 1 or X).
    var searchImpressions = [];
    $('[gtm-type="gtm-product-link"]', $(this)).each(function () {
      if (!$(this).hasClass('impression-processed')) {
        $(this).addClass('impression-processed');
        var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
        impression.list = 'Search Results Page';
        impression.position = $(this).attr('data-insights-position');
        // Keep variant empty for impression pages. Populated only post add to cart action.
        impression.variant = '';
        searchImpressions.push(impression);

        $(this).once('js-event').on('click', function (e) {
          var that = $(this);
          var position = $(this).attr('data-insights-position');
          Drupal.alshaya_seo_gtm_push_product_clicks(that, drupalSettings.reactTeaserView.price.currency, 'Search Results Page', position);
        });
      }
    });

    if (searchImpressions.length > 0) {
      Drupal.alshaya_seo_gtm_push_impressions(drupalSettings.reactTeaserView.price.currency, searchImpressions);
    }
  });
  // Push Filter event to GTM.
  $('body').once('bind-facet-item-click').on('click','.facet-item', function(event) {
    if (!$(this).hasClass('is-active')) {
      var selectedVal = document.createElement('div');
      var facetTitle = $(this).attr('datadrupalfacetlabel');
      selectedVal.innerHTML = $(this).find('span.facet-item__value').html();
      selectedVal.querySelectorAll('span.facet-item__count').forEach(function(item, index){
        item.parentNode.removeChild(item);
      });
      var data = {
        event: 'filter',
        siteSection: 'search results',
        filterType: facetTitle,
        filterValue: Drupal.t(selectedVal.innerHTML),
      };

      dataLayer.push(data);
    }
  });

})(jQuery, Drupal, dataLayer);
