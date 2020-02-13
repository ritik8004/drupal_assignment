/**
 * @file
 * JS code to integrate with GTM for Product into search list.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManagerSearchList = {
    attach: function (context, settings) {
      // Trigger incase of filter selected.
      $(document).once('alshaya-seo-gtm-search-filter').ready(function(context, settings) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-search'), settings);
      });

      $(window).once('alshaya-seo-gtm-product-search').on('scroll', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-search'), settings);
      });
    }
  };
})(jQuery, Drupal);
