(function ($) {
  "use strict";
  Drupal.behaviors.alshayaNewsletter = {
    attach: function (context, settings) {

      // Create a new instance of ladda for the specified button.
      var l = $('.edit-newsletter').ladda();

      $('.edit-newsletter').on('click', function() {
        // Start loading
        l.ladda('start');
      });

      $.fn.stopNewsletterSpinner = function(data) {
        l.ladda('stop');
      };
    }
  };
})(jQuery);
