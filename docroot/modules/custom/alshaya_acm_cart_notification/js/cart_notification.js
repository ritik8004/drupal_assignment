(function ($) {
  "use strict";
  Drupal.behaviors.alshayaAcmCartNotification = {
    attach: function (context, settings) {

      $.fn.cartNotificationScroll = function() {
        $('html,body').animate({
          scrollTop: $('.header--wrapper').offset().top
        }, 'slow');
      };

      $(window).on('click', function() {
        // check if element is Visible
        var element = $('#cart_notification');
        var length = $('#cart_notification').html().length;
        if (length > 0) {
          $('#cart_notification').empty();
        }
      });
      // Stop event from inside container to propogate out.
      $('#cart_notification').on('click', function(event){
        event.stopPropagation();
      });

      var btn = document.querySelector('#edit-add-to-cart');
      var l = Ladda.create(btn);

      btn.addEventListener('click', function() {
        l.start();
      });

      btn.addEventListener('mousedown', function() {
        l.start();
      });

      btn.addEventListener('keydown', function(event) {
        if (event.keyCode == 13 || event.keyCode == 32) {
          l.start();
        }
      });

      $.fn.stopSpinner = function() {
        l.stop();
      };
    }
  };
})(jQuery);
