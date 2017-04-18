(function ($) {
  "use strict";
  Drupal.behaviors.alshaya_acm_cart_notification = {
    attach: function (context, settings) {
      $(window).click(function() {
        // check if element is Visible
        var element = $('#cart_notification');
        var length = $('#cart_notification').html().length;
        if (length > 0) {
          $('#cart_notification').empty();
        }
      });
      // Stop event from inside container to propogate out.
      $('#cart_notification').click(function(event){
        event.stopPropagation();
      });
    }
  };
})(jQuery);
