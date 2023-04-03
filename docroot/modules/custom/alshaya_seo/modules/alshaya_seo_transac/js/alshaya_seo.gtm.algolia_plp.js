/**
 * @file
 * JS code to integrate with GTM for Algolia.
 */

(function ($, Drupal, dataLayer, debounce, drupalSettings) {

  Drupal.behaviors.algoliaPLP = {
    attach: function (context, settings) {
      $('#alshaya-algolia-plp').once('seoGoogleTagManager').on('plp-results-updated', function (event, results) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('#alshaya-algolia-plp'), drupalSettings, event);

        $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', $('#alshaya-algolia-plp')).once('product-list-clicked').on('click', function (e) {
          // Product Click GTM event should not be triggered
          // when adding/removing from cart, when color swatch or
          // add to cart button is clicked and when adding/removing
          // product from wishlist.
          if (!$(e.target).closest('.swatches').length
            && !$(e.target).closest('.addtobag-button-container').length
            && !$(e.target).closest('.wishlist-button-wrapper').length) {
            Drupal.alshaya_seo_gtm_push_product_clicks(
              $(this),
              drupalSettings.gtm.currency,
              $('body').attr('gtm-list-name'),
              parseInt($(this).attr('data-insights-position'))
            );
          }
        });
      });

      $(window).once('alshaya-seo-gtm-product-plp-algolia').on('scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('#alshaya-algolia-plp'), drupalSettings, event);
      }, 500));

    }
  };

  // Push Filter event to GTM.
  $('#alshaya-algolia-plp').once('bind-facet-item-click').on('click','.facet-item', function () {
    var section = $('body').attr('gtm-list-name');
    if (section.indexOf('PLP') > -1) {
      section = $('h1.c-page-title', $('#block-page-title')).text().toLowerCase().trim();
    }

    if (!$(this).hasClass('is-active')) {
      var selectedText = '';
      var eventName = '';
      var facetTitle = $(this).attr('datadrupalfacetlabel');
      if ($(this).find('span.facet-item__value').length > 0) {
        selectedText = $(this).find('span.facet-item__value span.facet-item__label').html();
        if (selectedText === undefined) {
          selectedText = facetTitle;
          facetTitle = 'Category';
        }
        // For rating filter.
        if (facetTitle === 'Rating') {
          selectedText = $(this).find('span.facet-item__value div.listing-inline-star div.rating-label').html();
        }
        eventName = 'filter';
      }
      else {
        var selectedText = $(this).attr('gtm-key');
        // If selectedText is still empty it means either 'gtm-key' attr
        // is missing or is emppty so we use the sort option text
        // with Arabic text for AR and English text for EN version.
        if (!Drupal.hasValue(selectedText)) {
          selectedText = $(this).find('a.facet-item__value').html();
        }
        eventName = 'sort';
        facetTitle = 'Sort By';
      }

      var data = {
        event: eventName,
        siteSection: section,
        filterType: facetTitle,
        filterValue: selectedText,
      };
      dataLayer.push(data);
    }
  });

  $('#alshaya-algolia-plp').once('bind-loadmore-button-click').on('click', '.pager button', function () {
    var statsText = $('.pager .ais-Stats-text').attr('gtm-pagination-stats');
    // Push load more products click event to GTM.
    Drupal.alshayaSeoGtmPushEcommerceEvents({
      eventAction: 'plp clicks',
      eventLabel: 'load more products',
      eventLabel2: Drupal.hasValue(statsText) ? statsText : '',
    });
  });
})(jQuery, Drupal, dataLayer, Drupal.debounce, drupalSettings);
