/**
 * @file
 * JavaScript behaviors for acq_checkoutcom.js.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.acqCheckoutComForm = {
    attach: function attach(context) {

      $('.checkoutcom-credit-card-exp-year-select').once('current-year').on('change', function () {
        var month = $('.checkoutcom-credit-card-exp-month-select');
        var currentYear = (new Date()).getFullYear().toString();
        var selectedYear = $(this).find('option:selected').val();

        if (currentYear === selectedYear) {
          var currentMonth = (new Date()).getMonth();

          month.find('option').each(function () {

            if (parseInt($(this).val()) <= currentMonth) {
              if ($(this).is(':selected')) {
                $(this).next().prop('selected', true);
              }
              $(this).prop('disabled', true);
            }
          });

        }
        else {
          month.find('option').prop('disabled', false);
        }

        // Let other JS libraries know options are changed.
        month.trigger('change');
      }).trigger('change');

      $('#edit-actions-next', context).once('checkout-click').on('click', function (e) {
        e.preventDefault();

        var form = $(this).closest('form');
        if (!$(form).valid()) {
          return;
        }

        var paymentForm = $(this).parents('form');

        CheckoutKit.createCardToken({
          "name": cardName.value,
          "number": cardNumber.value,
          "expiryMonth": cardMonth.value,
          "expiryYear": cardYear.value,
          "cvv": cardCvv.value,
          "udf3": saveCard.value ? 'storeInVaultOnSuccess' : ''
        },function(data){
          $('#payment_details_checkout_com').hide();

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
