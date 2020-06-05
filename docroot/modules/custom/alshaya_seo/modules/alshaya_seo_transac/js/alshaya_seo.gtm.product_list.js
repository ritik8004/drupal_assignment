/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal, debounce) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManagerProductList = {
    attach: function (context, settings) {
      // Trigger incase of page load & filter selected from PLP.
      $(window).once('alshaya-seo-gtm-product-list').on('scroll load', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('.view-alshaya-product-list'), settings, event);
      }, 500));
      $(window).once('alshaya-seo-gtm-product-list-unload').on('unload', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('.view-alshaya-product-list'), settings, event);
      });
    }
  };
})(jQuery, Drupal, Drupal.debounce);
