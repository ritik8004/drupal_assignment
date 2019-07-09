/**
 * @file
 * JavaScript behaviors for acq_checkoutcom.js.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.acqCheckoutComForm = {
    attach: function attach(context) {
      $('#edit-actions-next', context).once('checkout-click').on('click', function (e) {
        e.preventDefault();
        var paymentForm = $(this).parents('form');

        CheckoutKit.createCardToken({
          "name": cardName.value,
          "number": cardNumber.value,
          "expiryMonth": cardMonth.value,
          "expiryYear": cardYear.value,
          "cvv": cardCvv.value
        },function(data){
          cardToken.value = data.id;
          cardBin.value = data.card.bin;
          cardName.value = '';
          cardNumber.value = '';
          cardCvv.value = '';
          cardMonth.value = '';
          cardYear.value = '';
          paymentForm.submit();
        })
      });

    }
  };

})(jQuery, Drupal, drupalSettings);
