/**
 * @file
 * JavaScript behaviors of acq_checkoutcom.form.js.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.checkoutComProcessed = false;
  Drupal.checkoutComTokenised = false;
  var oldBillingAddress = '';
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

    // Collect data to be processed.
    var billingAddress = $(form).find('input:not(.checkoutcom-input), select:not(.checkoutcom-input)').serialize();

    // Validate form only when there's a change and form has any validation error.
    if (oldBillingAddress !== billingAddress || Drupal.checkoutComProcessed === false) {
      // Store current billing address to validate again if there are any
      // change in already validated form.
      oldBillingAddress = billingAddress;

      // Validate checkout.com payment form.
      Drupal.ajax({
        url: Drupal.url('checkoutcom/submit/payment-form'),
        element: $('#edit-actions-next').get(0),
        base: false,
        progress: {type: 'throbber'},
        submit: billingAddress,
        dataType: 'json',
        type: 'POST',
      }).execute();
    }
  }

  // Helper method that will place errors.
  Drupal.checkoutComShowError = function (element, error) {
    var errorDiv = $('<div class="form-item--error-message" />');
    errorDiv.html(error);
    element.parent().append(errorDiv);
  };

  // Helper method to display global error.
  Drupal.checkoutComShowGlobalError = function (error) {
    Drupal.checkoutComProcessed = false;
    var errorWrapper = $('<div class="messages__wrapper layout-container checkoutcom-global-error" />');
    var errorDiv = $('<div class="messages messages--error"></div>').html(error);
    errorWrapper.append(errorDiv);
    $('#payment_details_checkout_com').parents('form').find('.checkoutcom-global-error').remove();
    $('#payment_details_checkout_com').parents('form').prepend(errorWrapper);
    window.scrollTo(0, 0);
  };

  // Handle api error which triggered on card tokenisation fail.
  CheckoutKit.addEventHandler(CheckoutKit.Events.API_ERROR, function(event) {
    if (event.data.errorCode === '70000') {
      Drupal.checkoutComTokenisesd = false;
      Drupal.checkoutComShowGlobalError(Drupal.t('Transaction failed. Please try again or contact our customer service team for assistance.'));
    }
  });

  // Try to create card token for checkout.com if it's not already generated.
  $.fn.checkoutComCreateCardToken = function() {
    if ($('#cardNumber').length === 0) {
      Drupal.checkoutComTokenised = true;
      return;
    }

    var cardInfo = $('.payment_card_new').find('input.checkoutcom-input, select.checkoutcom-input').serialize();
    if (oldCardInfo !== cardInfo) {
      oldCardInfo = cardInfo;
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
    });
  };

  // Display errors for form fields.
  $.fn.checkoutPaymentError = function (formErrors) {
    Drupal.checkoutComProcessed = false;
    for (const errorFieldName in formErrors) {
      Drupal.checkoutComShowError($('[name="'+ errorFieldName +'"]'), formErrors[errorFieldName]);
    }
  };

  // Submit form on success.
  $.fn.checkoutPaymentSuccess = function () {
    Drupal.checkoutComProcessed = true;
    // Wait for tokenisation before submitting form.
    new Promise(function (resolve, reject) {
      var wait_for_tokenisation = setInterval(function () {
        if (Drupal.checkoutComTokenised === true) {
          clearInterval(wait_for_tokenisation);
          resolve();
        }
      }, 100);
    }).then(function () {
      document.getElementById('payment_details_checkout_com').style.display = 'none';

      if ($('.checkoutcom-input').length > 0) {
        $('.checkoutcom-input').each(function () {
          $(this).val('');
        });
      }
      $('#payment_details_checkout_com').parents('form').submit();
    });
  };

})(jQuery, Drupal, drupalSettings);
