/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal, debounce) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManagerProductList = {
    attach: function (context, settings) {
      // Trigger incase of page load & filter selected from PLP.
      Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-alshaya-product-list'), settings);

      $(window).once('alshaya-seo-gtm-product-list').on('scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-alshaya-product-list'), settings);
      }, 500));
    }
  };
})(jQuery, Drupal, Drupal.debounce);
