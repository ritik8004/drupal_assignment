/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManagerProductList = {
    attach: function (context, settings) {
      // Trigger incase of filter selected.
      $(document).once('alshaya-seo-gtm-plp-filter').ready(function(context, settings) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-alshaya-product-list'), settings);
      });

      $(window).once('alshaya-seo-gtm-product-list').on('scroll', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-alshaya-product-list'), settings);
      });
    }
  };
})(jQuery, Drupal);
