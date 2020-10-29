/**
 * @file
 * JS code to integrate with GTM for Algolia.
 */

(function ($, Drupal, dataLayer, debounce, drupalSettings) {
  'use strict';

  Drupal.behaviors.algoliaPLP = {
    attach: function (context, settings) {
      $('#alshaya-algolia-plp').once('seoGoogleTagManager').on('plp-results-updated', function (event, results) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('#alshaya-algolia-plp'), drupalSettings, event);

        $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', $('#alshaya-algolia-plp')).once('product-list-clicked').on('click', function () {
          var that = $(this);
          var position = parseInt($(this).closest('.views-row').data('list-item-position'));
          Drupal.alshaya_seo_gtm_push_product_clicks(that, drupalSettings.gtm.currency, $('body').attr('gtm-list-name'), position);
        });
      });

      $(window).once('alshaya-seo-gtm-product-plp-algolia').on('scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('#alshaya-algolia-plp'), drupalSettings, event);
      }, 500));

    }
  };

  // Push Filter event to GTM.
  $('#alshaya-algolia-plp').once('bind-facet-item-click').on('click','.facet-item', function (event) {

    var section = $('body').attr('gtm-list-name');
    if (section.indexOf('PLP') > -1) {
      section = $('h1.c-page-title', $('#block-page-title')).text().toLowerCase().trim();
    }

    if (!$(this).hasClass('is-active')) {
      var selectedText = $(this).find('span.facet-item__value').length > 0 ? $(this).find('span.facet-item__value').html() : $(this).find('a.facet-item__value').html();
      var facetTitle = $(this).attr('datadrupalfacetlabel');
      var selectedVal = selectedText.split(/<(.*?)>/)

      var data = {
        event: 'filter',
        siteSection: section,
        filterType: facetTitle,
        filterValue: selectedVal.length > 0 ? selectedVal[0] : '',
      };
      dataLayer.push(data);
    }
  });

})(jQuery, Drupal, dataLayer, Drupal.debounce, drupalSettings);
