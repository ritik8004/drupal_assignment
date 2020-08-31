/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal) {
  'use strict';

  $(window).on('load', function() {
    // Trigger productDetailView event.
    Drupal.alshayaSeoGtmPushProductDetailView($('#pdp-layout'));
  });

})(jQuery, Drupal);
