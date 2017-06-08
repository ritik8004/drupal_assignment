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

      // Create a new instance of ladda for the specified button
      $('.edit-add-to-cart').attr( 'data-style', 'zoom-in');
      var l = $('.edit-add-to-cart').ladda();

      $('.edit-add-to-cart').on('click', function() {
        // Start loading
        l.ladda( 'start' );
      });

      $('.edit-add-to-cart').on('mousedown', function() {
        // Start loading
        l.ladda( 'start' );
      });

      $('.edit-add-to-cart').on('keydown', function(event) {
        if (event.keyCode == 13 || event.keyCode == 32) {
          // Start loading
          l.ladda('start');
        }
      });

      $('[data-drupal-selector="edit-configurables-size"]', context).on('change', function() {
        // Start loading
        l.ladda( 'start' );
      });

      $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.extraData._triggering_element_name === "configurables[size]") {
          $(this).stopSpinner(['success']);
        }
      });

      $.fn.stopSpinner = function(data) {
        l.ladda('stop');
        if (data.message === 'success') {
          $('.ladda-label').html(Drupal.t('added'));
        }
        else {
          $('.ladda-label').html(Drupal.t('error'));
        }
        setTimeout(
          function() {
            $('.ladda-label').html(Drupal.t('add to cart'));
          }, data.interval);
      };

    }
  };

})(jQuery);
