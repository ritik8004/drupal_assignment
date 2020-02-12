/**
 * @file
 * JS code to integrate with GTM for Product into search list.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManagerSearchList = {
    attach: function (context, settings) {
      // Trigger incase of filter selected.
      drupalSettings.alshayaSeoGtmFilTertriggered = false;
      $(context).once('alshaya-seo-gtm-filter-search').ready(function(context, settings) {
        if ( drupalSettings.alshayaSeoGtmFilTertriggered ) {
          return;
        }
        drupalSettings.alshayaSeoGtmFilTertriggered = true;
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-search'), settings);
      });

      $(window).on('load scroll', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression($('.view-search'), settings);
      });
    }
  };
})(jQuery, Drupal);
