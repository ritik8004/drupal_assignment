/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManagerProductList = {
    attach: function (context, settings) {
      // Don't allow for aloglia search.
      if(window.location.href.match(/#query/g)) {
        return;
      }
      // Trigger incase of filter selected.
      drupalSettings.alshayaSeoGtmFilTertriggered = false;
      $(context).once('alshaya-seo-gtm-filter-search').ready(function(context, settings) {
        if ( drupalSettings.alshayaSeoGtmFilTertriggered ) {
          return;
        }
        drupalSettings.alshayaSeoGtmFilTertriggered = true;
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(context, settings);
      });

      $(window).on('load scroll', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(context, settings);
      });
    }
  };
})(jQuery, Drupal);
