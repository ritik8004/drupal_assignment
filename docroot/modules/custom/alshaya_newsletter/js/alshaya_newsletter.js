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
      };
    }
  };

  // Clear newsletter form success message.
  Drupal.clearNewsletterForm = function () {
    $('#footer-newsletter-form-wrapper').html('');
  };

})(jQuery);
