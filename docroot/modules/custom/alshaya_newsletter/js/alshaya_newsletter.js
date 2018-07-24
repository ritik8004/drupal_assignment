(function ($) {
  'use strict';
  Drupal.behaviors.alshayaNewsletter = {
    attach: function (context, settings) {

      // Create a new instance of ladda for the specified button.
      var l = $('.edit-newsletter').ladda();

      $('.edit-newsletter').on('click', function () {
        // Start loading
        l.ladda('start');
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

      // On focus out on button click.
      $('.block-alshaya-newsletter-subscription button.edit-newsletter').on('focusout', function() {
        // If email field is empty.
        if ($('.block-alshaya-newsletter-subscription input[name="email"]').val().length < 1) {
          // If there was an error due to ajax response, then remove it.
          if ($('.block-alshaya-newsletter-subscription #footer-newsletter-form-wrapper span.message').hasClass('error')) {
            $('.block-alshaya-newsletter-subscription #footer-newsletter-form-wrapper .subscription-status').remove()
            $('.block-alshaya-newsletter-subscription input[name="email"]').removeClass('error');
          }
        }
      });
    }
  };

  // Clear newsletter form success message.
  Drupal.clearNewsletterForm = function () {
    $('#footer-newsletter-form-wrapper').html('');
  };

})(jQuery);
