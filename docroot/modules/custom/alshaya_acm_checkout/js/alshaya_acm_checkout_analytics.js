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
    if (typeof ga == 'undefined') {
      setTimeout(Drupal.alshayaPopulateDataFromGA, 500);
      return;
    }

    try {
      $('#acm-ga-client-id').val(ga.getAll()[0].get('clientId'));
      $('#acm-ga-tracking-id').val(ga.getAll()[0].get('trackingId'));
    } catch (e) {
    }
  };

})(jQuery, Drupal);
