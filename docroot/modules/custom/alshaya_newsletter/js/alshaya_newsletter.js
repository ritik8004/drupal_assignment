(function ($) {
  "use strict";
  Drupal.behaviors.alshayaNewsletter = {
    attach: function (context, settings) {

      // Create a new instance of ladda for the specified button.
      var l = $('.edit-newsletter').ladda();

      $('.edit-newsletter').on('click', function() {
        if($('#alshaya-newsletter-subscribe .form-type-email label.error').is(':visible') === true) {
          $('#alshaya-newsletter-subscribe .form-type-email').addClass('inline-error');
        }
        else {
          $('#alshaya-newsletter-subscribe .form-type-email').removeClass('inline-error');
        }
        // Start loading
        l.ladda('start');
      });

      $.fn.stopNewsletterSpinner = function(data) {
        l.ladda('stop');
      };
    }
  };
})(jQuery);
