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
      $('.cybersource-input').once('security').on('copy paste', function (event) {
        event.preventDefault();
      });

      $('.cybersource-credit-card-exp-year-select').once('current-year').on('change', function () {
        var month = $('.cybersource-credit-card-exp-month-select');
        var currentYear = (new Date()).getFullYear().toString();
        var selectedYear = jQuery('.cybersource-credit-card-exp-year-select option:selected').val();

        if (currentYear === selectedYear) {
          var currentMonth = (new Date()).getMonth();

          month.find('option').each(function () {
            var optionMonth = parseInt($(this).val());

            if (optionMonth <= currentMonth) {
              if ($(this).is(':selected')) {
                $(this).next().prop('selected', true);
              }
              $(this).prop('disabled', true);
            }
          });

        }
        else {
          month.find('option').prop('disabled', false);
          month.val(month.find("option:first").val());
        }

        // Let other JS libraries know options are changed.
        month.trigger('change');
      });

      $('.cybersource-credit-card-exp-year-select').trigger('change');

      // Bind this only once after every ajax call.
      $('.cybersource-credit-card-input').once('bind-events').each(function () {
        var form = $('.cybersource-credit-card-input').closest('form');
        var wrapper = $('.cybersource-credit-card-input').closest('#payment_details');

        // Remove the name attributes to ensure it is not posted to server even by mistake.
        $(wrapper).find('input:text, input:password, select').each(function () {
          $(this).data('name', $(this).attr('name'));
          $(this).removeAttr('name');
        });

        $(form).once('bind-client-side').each(function () {
          try {
            // Update the validate settings to use custom submit handler.
            var validator = $(form).validate();
            validator.settings.submitHandler = cybersource_form_submit_handler;
          }
          catch (e) {
            // If any error comes we reload the page.
            // JS is very critical for cybersource to work.
            window.location.reload();
          }
        });

        $(this).validateCreditCard(function (result) {
          // Reset the card type every-time.
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
    var wrapper = $('.cybersource-credit-card-input').closest('#payment_details');

    // We hide credit card fields to avoid error message displays when we remove value.
    $(wrapper).hide();

    // We don't pass credit card info to drupal.
    $(wrapper).find('input:text, input:password, select').each(function () {
      $(this).val('-');

      // Add the name attribute again to ensure server side validation doesn't break.
      $(this).attr('name', $(this).data('name'));
    });

    // Reset expiry year and month dropdowns.
    $(wrapper).find('select').each(function () {
      $(this).val($(this).find('option:first').val());
    });

    // Update the JS to ensure we don't submit to cybersource again.
    Drupal.cybersourceProcessed = true;

    // Hide the payment button now to avoid double click from user.
    $('[data-drupal-selector="edit-actions-next"]').closest('form').find('.form-submit').hide();

    // Proceed with payment now.
    $('[data-drupal-selector="edit-actions-next"]').trigger('click');
  };

  Drupal.cybersourceShowError = function (element, error) {
    var errorDiv = $('<div class="form-item--error-message" />');
    errorDiv.html(error);

    element.parent().append(errorDiv);

    // Remove the loader, we will add it again.
    $('.cybersource-ajax-progress-throbber').remove();
  };

  Drupal.cybersourceShowGlobalError = function (error) {
    Drupal.cybersourceProcessed = false;

    $('.cybersource-input').parents('form').find('.cybersource-global-error').remove();
    $('.cybersource-input').parents('form').prepend(error);
    window.scrollTo(0, 0);

    // Remove the loader, we will add it again.
    $('.cybersource-ajax-progress-throbber').remove();
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
  var card_expiry_date_month = $('.cybersource-credit-card-exp-month-select option:selected').val().toString().trim();
  var card_expiry_date_year = $('.cybersource-credit-card-exp-year-select option:selected').val().toString().trim();

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

  // Remove the loader div to avoid duplicates.
  $('.cybersource-ajax-progress-throbber').remove();

  // Add the loader div.
  $('body').append('<div class="cybersource-ajax-progress cybersource-ajax-progress-throbber"><div class="cybersource-throbber"></div></div>');

  // Show the loader.
  $('.cybersource-ajax-progress-throbber').show();

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
      response.data.card_number = $('.cybersource-credit-card-input').val().toString();
      // Remove valid data per formatting but invalid for number.
      response.data.card_number = response.data.card_number.replace(/\s|-/g, '');
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
