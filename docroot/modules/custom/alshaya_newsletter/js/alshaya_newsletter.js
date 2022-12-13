(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaNewsletter = {
    attach: function (context, settings) {

      // Create a new instance of ladda for the specified button.
      var l = $('.edit-newsletter').ladda();

      $('.edit-newsletter').once().on('click', function (e) {
        var data = [];
        // Event prevent the form submission.
        e.preventDefault();
        // Start loading
        l.ladda('start');
        // Execute the validation and API call with some delay so that we have
        // the ladda loader in place.
        setTimeout(function () {
          // Get the value of email from the form.
          data['email'] = $("#alshaya-newsletter-subscribe #edit-email").val();
          if (!data['email']) {
            data['message'] = 'failure';
            data['html'] = '<div class="subscription-status"><span class="message error">' + Drupal.t('Please enter an email address') + '</span></div>';
          }
          // Update the interval time in the data array.
          data['interval'] = drupalSettings.newsletter.ajaxSpinnerMessageInterval;
          // Call the API to subscribe to the newsletter.
          var message = '';
          // Proceed only if email is present & form is not having any existing
          // error.
          if (data['email'] && !$("#edit-email-error").html()) {
            // Validate the newsletter API and show the proper error message based
            // on the fulfilling criteria.
            try {
              // Call the validate API.
              $.ajax({
                url: drupalSettings.cart.url + drupalSettings.newsletter.apiUrl,
                type: 'POST',
                dataType: 'json',
                processData: false,
                async: false,
                contentType: 'application/json',
                data: JSON.stringify({ email: data['email'] }),
                success: function (response) {
                  if (!response.is_subscribed) {
                    // Validate if the email id is subscribed or not.
                    message = '<span class="message success">' + Drupal.t('Thank you for your subscription.') + '</span>';
                    data['message'] = 'success';
                  } else {
                    // Show the error message saying the email is already subscribed.
                    data['message'] = 'failure';
                    message = '<span class="message error">' + Drupal.t('This email address is already subscribed.') + '</span>';
                  }
                },
                error: function (xhr, textStatus, error) {
                  // Log the error message.
                  Drupal.logJavascriptError('Something went wrong', error);
                  message = '<span class="message error">' + drupalSettings.globalErrorMessage + '</span>';
                  data['message'] = 'failure';
                }
              });
            } catch (err) {
              // Log the error message.
              Drupal.logJavascriptError('Something went wrong', err);
              message = '<span class="message error">' + drupalSettings.globalErrorMessage + '</span>';
              data['message'] = 'failure';
            }
            // Update the message in data.
            data['html'] = '<div class="subscription-status">' + message + '</div>';
          }

          // Call the response handler function with all the required data.
          $.fn.newsletterHandleResponse(data);
        }, 1);
      });

      /**
       * Hide multiple inline error messages for email field,
       * and Remove ajax error if present to prevent both inline and ajax error
       * being displayed at the same time.
       */
      $('#alshaya-newsletter-subscribe .form-type-email input').once().on('keyup', function () {
        var ajaxWrapper = '#alshaya-newsletter-subscribe #footer-newsletter-form-wrapper';
        if ($('#alshaya-newsletter-subscribe .form-type-email label.error').is(':visible') === true) {
          $(ajaxWrapper).empty();
        }

        if ($(ajaxWrapper).find('span.message').hasClass('error')) {
          $(ajaxWrapper).find('.subscription-status').remove();
        }
      });

      $.fn.newsletterHandleResponse = function (data) {
        $('#footer-newsletter-form-wrapper').html(data.html);
        l.ladda('stop');

        if (data.message === 'success') {
          // Tracking newsletter in gtm.
          if (Drupal.alshaya_seo_gtm_push_lead_type !== undefined) {
            Drupal.alshaya_seo_gtm_push_lead_type('footer');
          }
          $('#alshaya-newsletter-subscribe .form-type-email input').val('');
          setTimeout(Drupal.clearNewsletterForm, parseInt(data.interval));
        }
        else {
          // Add error class to the field.
          // We are doing this as we have made the field non-required as this
          // causing conflict/problem with the 'cv.jquery.validate.js' and thus
          // we need to add the class back.
          $('#alshaya-newsletter-subscribe .form-type-email input').addClass('error');
        }
      };

      //On focus-out from button/email field.
      $('.block-alshaya-newsletter-subscription button.edit-newsletter, .block-alshaya-newsletter-subscription input[name="email"]').on('focusout', function () {
        hideNewsLetterError();
      });

      /**
       * Hide/Remove error on focus out if email field empty.
       */
      var hideNewsLetterError = function () {
        // If email field is empty.
        if ($('.block-alshaya-newsletter-subscription input[name="email"]').val().length < 1) {
          // If there was an error due to ajax response, then remove it.
          if ($('.block-alshaya-newsletter-subscription #footer-newsletter-form-wrapper span.message').hasClass('error')) {
            $('.block-alshaya-newsletter-subscription #footer-newsletter-form-wrapper .subscription-status').remove()
          }

          // If error due to inline js error.
          if ($('.block-alshaya-newsletter-subscription label[for="edit-email"]').hasClass('error')) {
            $('.block-alshaya-newsletter-subscription label[for="edit-email"]').html('');
          }

          // Remove error class from the email field.
          $('.block-alshaya-newsletter-subscription input[name="email"]').removeClass('error');
        }
      }
    }
  };

  // Clear newsletter form success message.
  Drupal.clearNewsletterForm = function () {
    $('#footer-newsletter-form-wrapper').html('');
  };

})(jQuery, Drupal, drupalSettings);
