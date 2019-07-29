/**
 * @file
 * JavaScript behaviors of acq_checkoutcom.form.js.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.checkoutComTokenised = false;
  Drupal.checkoutComTokenisationProcessed = false;
  var oldCardInfo = '';

  Drupal.behaviors.acqCheckoutComForm = {
    attach: function attach(context) {
      $('.checkoutcom-credit-card-exp-year-select', context)
        .once('current-year')
        .on('change', function () {
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

          month.trigger('change');
        }).trigger('change');

      $('#payment_details_checkout_com', context)
        .once('process-validation')
        .each(function () {
          var form = $(this).closest('form');

          // Remove the name attributes to ensure it is not posted to server even by mistake.
          $(this).find('.payment_card_new').find('input:text, input:password, select').each(function () {
            $(this).data('name', $(this).attr('name'));
            $(this).removeAttr('name');
          });

          $(form).once('bind-client-side').each(function () {
            try {
              // Update the validate settings to use custom submit handler.
              var validator = $(form).validate();
              validator.settings.submitHandler = checkoutcom_form_submit_handler;
            }
            catch (e) {
              // If any error comes we reload the page.
              window.location.reload();
            }
          });
        });

      // Initialize checkout.kit with public key.
      $('.payment_card_new', context)
        .once('initialize-checkoutkit')
        .each(function() {
          CheckoutKit.configure({
            debugMode: (drupalSettings.checkoutCom.debug === 'true'),
            publicKey: drupalSettings.checkoutCom.public_key,
          });
        });

      // Bind this only once after every ajax call.
      $('.checkoutcom-credit-card-input')
        .once('bind-events')
        .each(function () {
          $(this).validateCreditCard(function (result) {
            // Reset the card type every-time.
            $(this).parent().removeClass('cc-error');
            $(this).removeClass('error');

            // Check if we have card_type.
            if (result.card_type !== null) {
              // Set error class on wrapper if invalid card number.
              if (!result.valid || !result.length_valid || !result.luhn_valid) {
                $(this).parent().addClass('cc-error');
                $(this).addClass('error');
              }
            }
          });
        });
    }
  };

  /**
   * Overridden Submit Handler to validate checkout payment form data on ajax.
   * @param form
   */
  function checkoutcom_form_submit_handler(form) {
    'use strict';
    var $ = jQuery.noConflict();

    // Submit form if card is tokenised and there are no form errors.
    if (Drupal.checkoutComTokenised === true && Drupal.checkoutComProcessed === true) {
      form.submit();
      return;
    }

    // Remove all error messages displayed right now before validating again.
    $(form).find('.form-item--error-message, label.error').remove();
    $(form).find('.checkoutcom-global-error').remove();

    // Reset tokenisation process, to verify it again on checkoutPaymentSuccess
    // to submit and move next step or clear the loader and show error.
    if (Drupal.checkoutComTokenised === false) {
      Drupal.checkoutComTokenisationProcessed = false;
    }

    Drupal.checkoutComValidateBeforeCheckout(form);
  }

  // Handle api error which triggered on card tokenisation fail.
  CheckoutKit.addEventHandler(CheckoutKit.Events.API_ERROR, function(event) {
    if (event.data.errorCode === '70000') {
      Drupal.checkoutComTokenisesd = false;
      Drupal.checkoutComShowGlobalError(Drupal.t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.'));
    }
  });

  // Try to create card token for checkout.com if it's not already generated.
  $.fn.checkoutComCreateCardToken = function() {
    if ($('#cardNumber').length === 0) {
      // When using tokenised card, we don't need to check for validations.
      Drupal.checkoutComTokenisationProcessed = true;
      Drupal.checkoutComTokenised = true;
      return;
    }

    var cardInfo = $('.payment_card_new').find('input.checkoutcom-input, select.checkoutcom-input').serialize();
    if (oldCardInfo !== cardInfo) {
      oldCardInfo = cardInfo;
      // As the card info is changed we don't want to create the new card token,
      // hence all validations are set to false.
      Drupal.checkoutComTokenisationProcessed = false;
      Drupal.checkoutComTokenised = false;
    }

    if (Drupal.checkoutComTokenised) {
      return;
    }

    CheckoutKit.createCardToken({
      'name': $('#cardName').val(),
      'number': $('#cardNumber').val(),
      'expiryMonth': $('#cardMonth').val(),
      'expiryYear': $('#cardYear').val(),
      'cvv': $('#cardCvv').val(),
      'udf3': ($('#saveCard').length > 0 && $('#saveCard').val()) ? 'storeInVaultOnSuccess' : ''
    },function(data){
      if (typeof data.card === 'undefined') {
        Drupal.checkoutComTokenised = false;
      }
      else {
        Drupal.checkoutComTokenised = true;
        $('#cardToken').val(data.id);
        $('#cardBin').val(data.card.bin);
      }
      Drupal.checkoutComTokenisationProcessed = true;
    });
  };

  // Submit form on success.
  $.fn.checkoutPaymentSuccess = function () {
    Drupal.checkoutComProcessed = true;
    // On resolve, submit form and redirect for confirmation.
    var promiseResolve = function () {
      document.getElementById('payment_details_checkout_com').style.display = 'none';

      if ($('.checkoutcom-input').length > 0) {
        $('.checkoutcom-input').each(function () {
          $(this).val('');
        });
      }
      $('#payment_details_checkout_com').parents('form').submit();
      $(document).trigger('checkoutcom_form_validated');
    };

    // Wait for tokenisation before submitting form.
    new Promise(function (resolve, reject) {
      $(this).showCheckoutLoader();
      var wait_for_tokenisation = setInterval(function () {
        if (Drupal.checkoutComTokenisationProcessed === true) {
          clearInterval(wait_for_tokenisation);
          (Drupal.checkoutComTokenised === true)
            ? resolve()
            : reject(new Error("checkout.com tokenisation failed."));
        }
      }, 100);
    }).then(promiseResolve);
  };

})(jQuery, Drupal, drupalSettings);
