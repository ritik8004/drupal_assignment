/**
 * @file
 * JS code to integrate with GTM for Product into sliders.
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.seoGoogleTagManagerProductList = {
    attach: function (context, settings) {
      // Don't allow for aloglia search.
      if(window.location.href.match(/#query/g)) {
        return;
      }

      $(document).once('bind-slick-slider').on('load scroll', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(context, settings);
      });

      $(document).once('bind-slick-slider-nav').on('click', '.view-product-slider .slick-prev, .view-product-slider .slick-next', function () {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(context, settings);
      } );
    }
  };

})(jQuery, Drupal);
