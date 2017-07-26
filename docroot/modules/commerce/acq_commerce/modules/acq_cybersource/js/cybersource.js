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

      $('[data-drupal-selector="edit-actions-next"]').once('bind-events').each(function () {
        $(this).on('click', function (event) {
          if (Drupal.cybersourceProcessed !== true) {
            event.preventDefault();
            event.stopPropagation();

            var card = $('.cybersource-credit-card-input').val().toString().trim();
            if (card == '') {
              // TODO: Handle error here.
              return;
            }

            var type = $('.cybersource-credit-card-type-input').val().toString().trim();

            if (type == '') {
              // TODO: Handle error here.
              return;
            }

            // @TODO: Check for basic required field validations at-least
            // before starting cybersource process.
            // Ajax POST request to get token.
            // We also send the address here to set billing info.
            // @TODO: Another one - this looks not generic.
            var getTokenData = $('[data-drupal-selector="edit-billing-address"]').serialize() + '&type=' + type;

            $.ajax({
              type: 'POST',
              cache: false,
              url: Drupal.url('cybersource/get-token'),
              data: getTokenData,
              dataType: 'json',
              success: function (response) {
                // @TODO: Check first here if we have valid token and data to send.
                // Add credit cart info.
                response.data.card_number = $('.cybersource-credit-card-input').val().toString().trim();
                response.data.card_expiry_date = $('.cybersource-credit-card-exp-month-select option:selected').val().toString().trim();
                response.data.card_expiry_date += '-';
                response.data.card_expiry_date += $('.cybersource-credit-card-exp-year-select option:selected').val().toString().trim();
                response.data.card_cvn = $('.cybersource-credit-card-cvv-input').val().toString().trim();

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

                for (var i in response.data) {
                  $("<input type='hidden' />").attr("name", i).attr("value", response.data[i]).appendTo(cybersourceForm);
                }

                var cybersourceIframe = $('<iframe style="display:none!important;" name="cybersource_iframe" id="cybersource_iframe"></iframe>');
                $('body').append(cybersourceIframe);
                $('body').append(cybersourceForm);
                cybersourceForm.submit();
              },
              error: function (xmlhttp, message, error) {
                // TODO: Handle error here.
                console.log(xmlhttp);
                console.log(message);
                console.log(error);
              }
            });
          }
        });

        $(this).parents('form:first').on('submit', function (event) {
          if (Drupal.cybersourceProcessed !== true) {
            event.preventDefault();
            event.stopPropagation();
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
  }

})(jQuery, Drupal);
