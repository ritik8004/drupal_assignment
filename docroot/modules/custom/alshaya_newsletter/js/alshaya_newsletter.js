(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaNewsletter = {
    attach: function (context, settings) {

      // Create a new instance of ladda for the specified button.
      var l = $('.edit-newsletter').ladda();

      $('.edit-newsletter').on('click', function () {
        // Doing this as we checking class and its not available immediately
        // due to race condition.
        setTimeout(function () {
          if (!$('form#alshaya-newsletter-subscribe').hasClass('ajax-submit-prevented')) {
            // Start loading
            l.ladda('start');
          }
        }, 10);
      });

      // Hide multiple inline error messages for email field.
      $('#alshaya-newsletter-subscribe .form-type-email input').once().on('keyup', function () {
        var ajaxWrapper = '#alshaya-newsletter-subscribe #footer-newsletter-form-wrapper';
        if ($('#alshaya-newsletter-subscribe .form-type-email label.error').is(':visible') === true) {
          $(ajaxWrapper).empty();
        }
      });

      $.fn.newsletterHandleResponse = function (data) {
        $('#footer-newsletter-form-wrapper').html(data.html);
        l.ladda('stop');

        if (data.message === 'success') {
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

      /**
       * Utility function to subscribe the newsletter.
       *
       * @param {array} data
       *   The array containing the email and some meta info.
       */
      $.fn.newsletterCallApi = function (data) {
        var message = '';
        // Proceed only if email is present.
        if (data['email']) {
          // Validate the newsletter API and show the proper error message based
          // on the fulfilling criteria.
          try {
            if (!Drupal.subscriberNewsletter(data['email'])) {
              // Validate if the email id is subscribed or not.
              message = '<span class="message success">' + Drupal.t('Thank you for your subscription.') + '</span>';
              data['message'] = 'success';
            } else {
              // Show the error message saying the email is already subscribed.
              data['message'] = 'failure';
              message = '<span class="message error">' + Drupal.t('This email address is already subscribed.') + '</span>';
            }
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
      }
    }
  };

  // Clear newsletter form success message.
  Drupal.clearNewsletterForm = function () {
    $('#footer-newsletter-form-wrapper').html('');
  };

  // Subscribe newsletter.
  Drupal.subscriberNewsletter = function(email) {
    var alreadySubscribed = false;
    // Call the validate API.
    $.ajax({
      url: drupalSettings.newsletter.apiUrl + '/V1/newsletter/subscription',
      type: 'POST',
      dataType: 'json',
      processData: false,
      async: false,
      contentType: 'application/json',
      data: JSON.stringify({ email : email }),
      success: function (response) {
        alreadySubscribed = response.is_subscribed;
      }
    });

    return alreadySubscribed;
  }

})(jQuery, Drupal, drupalSettings);
