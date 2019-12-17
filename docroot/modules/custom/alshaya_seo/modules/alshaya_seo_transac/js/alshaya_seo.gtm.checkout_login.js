/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  $('body[gtm-container="checkout login page"]').once('gtm_checkout_login').each(function () {
    // Tracking New customers.
    $('a[gtm-type="checkout-as-guest"]', $(this)).on('click', function () {
      Drupal.alshaya_seo_gtm_push_checkout_option('Guest Login', 1);
    });

    // Tracking Returning customers.
    $('a[gtm-type="checkout-signin"]', $(this)).on('mousedown', function () {
      Drupal.alshaya_seo_gtm_push_checkout_option('New Login', 1);
    });
  });

})(jQuery, Drupal, dataLayer);
