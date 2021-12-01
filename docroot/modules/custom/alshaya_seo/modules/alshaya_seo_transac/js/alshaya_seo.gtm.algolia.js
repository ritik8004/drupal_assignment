/**
 * @file
 * JS code to integrate with GTM for Algolia.
 */

(function ($, Drupal, dataLayer, debounce, drupalSettings) {
  'use strict';

  var searchQuery = [];
  var initNoOfResults = null;

  // Bind for Algolia Search page. No impact if Algolia search not enabled
  // as selector won't be available.
  $(document).once('seoGoogleTagManager').on('search-results-updated', '#alshaya-algolia-search', debounce(function (event, noOfResult) {
    // Allow for aloglia search result.
    if (!$('#alshaya-algolia-search').hasClass('show-algolia-result') && !$('#alshaya-algolia-search').is(':visible')) {
      return;
    }
    // Update the page view for SPA Search page.
    if (typeof window.DY !== 'undefined') {
      window.DY.API('spa', {
        context: {
          type: 'OTHER',
          lng: drupalSettings.dynamicYield.lng,
        },
        countAsPageview: true,
      });
    }
    Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_algolia_product_impression, $('#alshaya-algolia-search'), drupalSettings, event);
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

    $(window).once('alshaya-seo-gtm-product-search-algolia').on('scroll', debounce(function (event) {
      Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_algolia_product_impression, $('#alshaya-algolia-search'), drupalSettings, event);
    }, 500));
  }, drupalSettings.gtm.algolia_trigger_ga_after));

  Drupal.alshaya_seo_gtm_prepare_algolia_product_impression = function (context, eventType) {
    var searchImpressions = [];

    $('#alshaya-algolia-search [gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]:not(".impression-processed"):visible').each(function () {
      var condition = true;
      // Only on scroll we check if product is in view or not.
      if (eventType === 'scroll') {
        condition = $(this).isElementInViewPort(0, 10);
      }
      if (condition) {
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

        // When search results load, process only the default number of
        // items and push to datalayer.
        if ((eventType === 'search-results-updated')
          && (searchImpressions.length === (drupalSettings.gtm.productImpressionDefaultItemsInQueue))
        ) {
          // This is to break out from the .each() function.
          return false;
        }
      }
    });

    return searchImpressions;
  }

  // Push Filter event to GTM.
  $('body').once('bind-facet-item-click').on('click','.facet-item', function (event) {
    // To get page type for listing pages.
    // Search page does not have the pageSubType key in drupalSettings.
    var listName = drupalSettings.algoliaSearch.pageSubType ? drupalSettings.algoliaSearch.pageSubType : drupalSettings.path.currentPath;
    var section = $('h1.c-page-title', $('#block-page-title')).text().toLowerCase().trim();
    if (!$(this).hasClass('is-active')) {
      var selectedText = '';
      var eventName = '';
      var facetTitle = $(this).attr('datadrupalfacetlabel');
      if ($(this).find('span.facet-item__value').length > 0) {
        selectedText = $(this).find('span.facet-item__value span.facet-item__label').html();
        // For rating filter.
        if (facetTitle === 'Rating') {
          selectedText = $(this).find('span.facet-item__value div.listing-inline-star div.rating-label').html();
        }
        eventName = 'filter';
      }
      else {
        selectedText = $(this).find('a.facet-item__value').html();
        eventName = 'sort';
      }
      switch (listName) {
        case 'search':
          var data = {
            event: eventName,
            siteSection: 'search results',
            filterType: facetTitle,
            filterValue: selectedText,
          };
          break;

        case 'promotion':
        case 'product_option_list':
          var data = {
            event: eventName,
            siteSection: section,
            filterType: facetTitle,
            filterValue: selectedText,
          };
          break;
      }
      dataLayer.push(data);
    }
  });

})(jQuery, Drupal, dataLayer, Drupal.debounce, drupalSettings);
