/**
 * @file
 * JS code to integrate with GTM for Product into search list.
 */

(function ($, Drupal, debounce) {
  'use strict';

  Drupal.alshayaSeoGtmPushSearchEvent = function (context, settings) {
    $('.c-header #edit-keywords').once('internalsearch').each(function () {
      var keyword = Drupal.getQueryVariable('keywords');
      var noOfResult = parseInt($('.view-header', context).text().replace(Drupal.t('items'), '').trim());
      noOfResult = isNaN(noOfResult) ? 0 : noOfResult;

      var action = noOfResult === 0 ? '404 Results' : 'Successful Search';
      var interaction = noOfResult === 0 ? noOfResult : 1;

      dataLayer.push({
        event: 'eventTracker',
        eventCategory: 'Internal Site Search',
        eventAction: action,
        eventLabel: keyword,
        eventValue: noOfResult,
        nonInteraction: interaction,
      });
    });
  }

  Drupal.behaviors.seoGoogleTagManagerSearchList = {
    attach: function (context, settings) {
      // Trigger incase of page load & filter selected from SRP.
      $(window).once('alshaya-seo-gtm-product-search-load').on('load', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-search'), settings);
        Drupal.alshayaSeoGtmPushSearchEvent(context, settings);
      });
      $(window).once('alshaya-seo-gtm-product-search-scroll').on('scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-search'), settings);
      }, 500));
    }
  };
})(jQuery, Drupal, Drupal.debounce);
