(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for checkout flow.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for checkout flow.
   */
  Drupal.behaviors.ZZAlshayaAcmCheckout = {
    attach: function (context, settings) {
      // Select a payment method first to avoid 500 error when address ajax callback is called.
      // @TODO: Fix this to work flawlessly without doing such selections.
      if ($('.form-item-payment-methods-payment-options input:selected').length() === 0) {
        $('.form-item-payment-methods-payment-options:first input').trigger('click');
      }

      if ($('.same-as-shipping:checked').val() === '2') {
        $('div[data-drupal-selector="edit-billing-address-address"]').show();
      }
      else if ($('.same-as-shipping:checked').val() === '1') {
        $('div[data-drupal-selector="edit-billing-address-address"]').hide();
      }
      else {
        setTimeout('jQuery(".same-as-shipping[value=1]").trigger("click").trigger("change")', 250);
      }
    }
  };

})(jQuery, Drupal);
