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
          var cartRemoveElement = $(this).find('button.qty-sel-btn--down') !== undefined ? $(this).find('button.qty-sel-btn--down')[0] : null;
          // Product Click GTM event should not be triggered
          // when removing from cart.
          if (e.target !== cartRemoveElement) {
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
        selectedText = $(this).find('a.facet-item__value').html();
        eventName = 'sort';
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

  // Push search result grid buttons click event to GTM.
  $('#alshaya-algolia-plp').once('bind-grid-button-click').on('click', '.large-col-grid, .small-col-grid', function () {
    // Track large column grid click.
    if ($(this).hasClass('large-col-grid')) {
      Drupal.alshayaSeoGtmPushEcommerceEvents({
        eventAction: 'plp clicks',
        eventLabel: 'plp layout - large grid',
      });
    }
    // Track small column grid click.
    if ($(this).hasClass('small-col-grid')) {
      Drupal.alshayaSeoGtmPushEcommerceEvents({
        eventAction: 'plp clicks',
        eventLabel: 'plp layout - small grid',
      });
    }
  });

  $('#alshaya-algolia-plp').once('bind-loadmore-button-click').on('click', '.pager button', function () {
    let statsText = $('.pager .ais-Stats-text').attr('gtm-pagination-stats');
    // Push load more products click event to GTM.
    Drupal.alshayaSeoGtmPushEcommerceEvents({
      eventAction: 'plp clicks',
      eventLabel: 'load more products',
      eventLabel2: Drupal.hasValue(statsText) ? statsText : '',
    });
  });

})(jQuery, Drupal, dataLayer, Drupal.debounce, drupalSettings);
