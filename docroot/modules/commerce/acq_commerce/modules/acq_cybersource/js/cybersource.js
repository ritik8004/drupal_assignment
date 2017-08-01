(function ($, Drupal) {
  'use strict';

  Drupal.cybersourceProcessed = false;

  /**
   * All custom js for cybersource payment plugin.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for cybersource payment plugin.
   */
  Drupal.behaviors.checkoutCybersource = {
    attach: function (context, settings) {

      // Bind this only once after every ajax call.
      $('.cybersource-credit-card-input').once('bind-events').each(function () {
        // Update the validate settings to use custom submit handler.
        $('.cybersource-credit-card-input').parents('form').data('submit-handler', 'cybersource_form_submit_handler');
        Drupal.cvValidatorObjects[$('.cybersource-credit-card-input').parents('form').attr('id')].destroy();
        Drupal.behaviors.cvJqueryValidate.attach('body');


        $(this).validateCreditCard(function (result) {
          // Reset the card type ever-time.
          $('.cybersource-credit-card-type-input').val('');

          // Check if we have card_type.
          if (result.card_type !== null) {
            // Check if card number is valid.
            if (result.valid && result.length_valid && result.luhn_valid) {
              // Set the card type in hidden only if card number is valid.
              $('.cybersource-credit-card-type-input').val(result.card_type.name);
            }
          }
        });
      });
    }
  };


  Drupal.finishCybersourcePayment = function () {
    // We don't pass credit card info to drupal.
    $('.cybersource-credit-card-input').val('-');
    $('.cybersource-credit-card-cvv-input').val('-');

    // Update the JS to ensure we don't submit to cybersource again.
    Drupal.cybersourceProcessed = true;

    // Proceed with payment now.
    $('[data-drupal-selector="edit-actions-next"]').trigger('click');
  };

  Drupal.cybersourceShowError = function (element, error) {
    var errorDiv = $('<div class="form-item--error-message" />');
    errorDiv.html(error);

    if (element.is('input:checkbox')) {
      element.parent().append(errorDiv);
    }
    else {
      element.after(errorDiv);
    }
  };

  Drupal.cybersourceShowGlobalError = function (error) {
    Drupal.cybersourceProcessed = false;
    $('.cybersource-input').parents('form').find('.cybersource-global-error').remove();
    $('.cybersource-input').parents('form').prepend(error);
  }

})(jQuery, Drupal);


function cybersource_form_submit_handler(form) {
  'use strict';

  var $ = jQuery.noConflict();

  // Check again if we are still using cybersource.
  if ($('.cybersource-credit-card-type-input').length === 0) {
    // We are not using cybersource, we simply submit the form.
    form.submit();
  }

  // Remove all error messages displayed right now.
  $(form).find('.form-item--error-message, label.error').remove();
  $(form).find('.cybersource-global-error').remove();

  if (Drupal.cybersourceProcessed === true) {
    form.submit();
  }

  var formHasErrors = false;

  // Do sanity check of credit card number.
  var type = $('.cybersource-credit-card-type-input').val().toString().trim();
  if (type === '') {
    Drupal.cybersourceShowError($('.cybersource-credit-card-input'), Drupal.t('Invalid Credit Card number'));
    formHasErrors = true;
  }

  // Check cvv.
  var cvv = $('.cybersource-credit-card-cvv-input').val().toString().trim();
  if (isNaN(cvv) || cvv.length < 3 || cvv.length > 4) {
    Drupal.cybersourceShowError($('.cybersource-credit-card-cvv-input'), Drupal.t('Invalid security code (CVV)'));
    formHasErrors = true;
  }

  // Sanity check - expiry must be in future.
  var card_expiry_date_month = parseInt($('.cybersource-credit-card-exp-month-select option:selected').val().toString().trim());
  var card_expiry_date_year = parseInt($('.cybersource-credit-card-exp-year-select option:selected').val().toString().trim());

  var card_expiry_date = card_expiry_date_month + '-' + card_expiry_date_year;

  var today = new Date();
  var lastDayOfMonth = new Date(today.getFullYear(), today.getMonth()+1, 0);

  if (lastDayOfMonth.getFullYear() === card_expiry_date_year && lastDayOfMonth.getMonth() > card_expiry_date_month) {
    Drupal.cybersourceShowError($('.cybersource-credit-card-exp-year-select').parent(), Drupal.t('Incorrect credit card expiration date'));
    formHasErrors = true;
  }

  if (formHasErrors) {
    return false;
  }

  // Ajax POST request to get token.
  var getTokenData = 'card_type=' + type + '&';

  // We also send all other form data here to allow other modules to process based on that.
  getTokenData += $('.cybersource-input').parents('form').find('input:not(.cybersource-input), select:not(.cybersource-input)').serialize();

  $.ajax({
    type: 'POST',
    cache: false,
    url: Drupal.url('cybersource/get-token'),
    data: getTokenData,
    dataType: 'json',
    success: function (response) {
      if (response.errors) {
        for (var field in response.errors) {
          // Show the global level errors on top.
          if (field === 'global') {
            Drupal.cybersourceShowGlobalError(response.errors[field]);
          }
          else {
            Drupal.cybersourceShowError($('[name="' + field + '"]'), response.errors[field]);
          }
        }
        return;
      }

      // Add credit cart info.
      response.data.card_number = $('.cybersource-credit-card-input').val().toString().trim();
      response.data.card_expiry_date = card_expiry_date;
      response.data.card_cvn = parseInt($('.cybersource-credit-card-cvv-input').val().toString().trim());

      // Here we use iframe to post to Cybersource and then handle
      // the response in Drupal. We have configured the response url
      // in Cybersource profile.
      // This is necessary as Cybersource doesn't allow AJAX requests.
      // Even if it processes fine, it never sets the CORS header
      // and browser/javascript remove the response.
      // Remove old form and iframe if available.
      $('#cybersource_form_to_iframe, #cybersource_iframe').remove();

      // Create iframe and post to cybersource form there.
      var cybersourceForm = $('<form action="' + response.url + '" target="cybersource_iframe" method="post" style="display:none !important;" id="cybersource_form_to_iframe"></form>');

      for (var input in response.data) {
        $("<input type='hidden' />").attr('name', input).attr('value', response.data[input]).appendTo(cybersourceForm);
      }

      var cybersourceIframe = $('<iframe style="display:none!important;" name="cybersource_iframe" id="cybersource_iframe"></iframe>');
      $('body').append(cybersourceIframe);
      $('body').append(cybersourceForm);
      cybersourceForm.submit();
    },
    error: function (xmlhttp, message, error) {
      // TODO: Handle error here.
    }
  });
}
