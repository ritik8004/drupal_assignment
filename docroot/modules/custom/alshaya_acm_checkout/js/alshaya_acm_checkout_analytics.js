/**
 * @file
 */

(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for checkout flow analytics.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for checkout flow analytics.
   */
  Drupal.behaviors.alshayaAcmCheckoutAnalytics = {
    attach: function (context, settings) {
      Drupal.alshayaPopulateDataFromGA();
    }
  };

  Drupal.alshayaPopulateDataFromGA = function () {
    // Check if ga is loaded.
    if (typeof window.ga === 'function' && window.ga.loaded) {
      // Use GA function queue.
      ga(function () {
        $('#acm-ga-client-id').val(ga.getAll()[0].get('clientId'));
        $('#acm-ga-tracking-id').val(ga.getAll()[0].get('trackingId'));
      });

      return;
    }

    // Try to read again.
    setTimeout(Drupal.alshayaPopulateDataFromGA, 500);
  };

})(jQuery, Drupal);
