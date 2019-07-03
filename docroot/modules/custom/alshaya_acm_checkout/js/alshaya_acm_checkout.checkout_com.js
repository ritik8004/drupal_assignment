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
        $('.payment-card-wrapper-div', $(this)).find('.name-number').on('click', function () {
          var selected_option = $(this).data('value');
          $(this).showCheckoutLoader();
          // Check if this payment method is already active, if yes return.
          // We don't want to remove payment_details in this case else active payment form is lost.
          if ($(this).hasClass('card-selected')) {
            return false;
          }
          $(this).parent().siblings().removeClass('card-selected');
          $(this).addClass('card-selected');
          $('[data-drupal-selector="edit-acm-payment-methods-payment-details-wrapper-payment-method-checkout-com"]').find('input[value="' + selected_option + '"]').trigger('click');
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
