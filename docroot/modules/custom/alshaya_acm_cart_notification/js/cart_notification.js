(function ($, Drupal, document) {
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
      $('.edit-add-to-cart', context).attr( 'data-style', 'zoom-in');
      var l = $('.edit-add-to-cart').ladda();

      $('.edit-add-to-cart', context).on('click', function() {
        // Start loading
        $(this).ladda( 'start' );
      });

      $('.edit-add-to-cart', context).on('mousedown', function() {
        // Start loading
        $(this).ladda( 'start' );
      });

      $('.edit-add-to-cart', context).on('keydown', function(event) {
        if (event.keyCode == 13 || event.keyCode == 32) {
          // Start loading
          $(this).ladda('start');
        }
      });

      $('[data-drupal-selector="edit-configurables-size"]', context).on('change', function() {
        // Start loading.
        $(this).closest('#configurable_ajax').siblings('.ladda-button').ladda( 'start' );
      });

      $(document).ajaxComplete(function(event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) && (settings.extraData._triggering_element_name === "configurables[size]")) {
          $(this).stopSpinner(['success']);
        }
      });

      $.fn.stopSpinner = function(data) {
        l.ladda('stop');
        if (data.message === 'success') {
          $(this).find('.ladda-label').html(Drupal.t('added'));
          if ($('.ui-dailog')) {
            $('.ui-dialog .ui-dialog-titlebar-close').trigger('click');
          }
        }
        else if (data.message === 'failure') {
          $(this).find('.ladda-label').html(Drupal.t('error'));
        }
        setTimeout(
          function() {
            $(this).find('.ladda-label').html(Drupal.t('add to cart'));
          }, data.interval);
      };

    }
  };

})(jQuery, Drupal, document);
