/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.AlshayaCheckoutCom = {
    attach: function (context, settings) {

      // In order to show the form between radio buttons we do it
      // using custom markup. Here we update the radio buttons on
      // click of payment method names in custom markup.
      $('#payment_details_checkout_com').once('bind-events').each(function () {
        console.log('event bind');
        $('.payment-card-wrapper-div', $(this)).on('click', function () {
          var selected_option = $(this).data('value');
          console.log(selected_option);
          // Check if this payment method is already active, if yes return.
          // We don't want to remove payment_details in this case else active payment form is lost.
          if ($(this).hasClass('card-selected')) {
            return false;
          }
          $('[data-drupal-selector="edit-acm-payment-methods-payment-details-wrapper-payment-method-checkout-com"]').find('input[value="' + selected_option + '"]').trigger('click');
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
