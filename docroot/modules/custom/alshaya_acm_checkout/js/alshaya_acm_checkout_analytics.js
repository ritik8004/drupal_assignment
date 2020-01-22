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

      // Do it again once after page load finishes.
      // At times GA script loads late.
      $(window).once('alshayaAcmCheckoutAnalytics').on('load', function () {
        Drupal.alshayaPopulateDataFromGA();
      })
    }
  };

  Drupal.alshayaPopulateDataFromGA = function () {
    if (typeof ga == 'undefined') {
      setTimeout(Drupal.alshayaPopulateDataFromGA, 500);
      return;
    }

    try {
      $('#acm-ga-client-id').val(ga.getAll()[0].get('clientId'));
      $('#acm-ga-tracking-id').val(ga.getAll()[0].get('trackingId'));
    } catch (e) {
      // Try to do again if we face an error.
      setTimeout(Drupal.alshayaPopulateDataFromGA, 1000);
    }
  };

})(jQuery, Drupal);
