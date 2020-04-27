/**
 * @file
 * JS code to integrate with GTM for Product into search list.
 */

(function ($, Drupal, debounce) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManagerSearchList = {
    attach: function (context, settings) {
      // Trigger incase of page load & filter selected from SRP.
      $(window).once('alshaya-seo-gtm-product-search-load').on('load', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-search'), settings);
        Drupal.alshaya_seo_gtm_push_search_event(context, settings);
      });
      $(window).once('alshaya-seo-gtm-product-search-scroll').on('scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-search'), settings);
      }, 500));
    }
  };
})(jQuery, Drupal, Drupal.debounce);
