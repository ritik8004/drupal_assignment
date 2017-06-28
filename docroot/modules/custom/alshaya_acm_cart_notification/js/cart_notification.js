(function ($, Drupal, document) {
  'use strict';

  Drupal.behaviors.alshayaAcmCartNotification = {
    attach: function (context, settings) {

      $(window).on('click', function () {
        // check if element is Visible
        var length = $('#cart_notification').html().length;
        if (length > 0) {
          $('#cart_notification').empty();
        }
      });

      // Stop event from inside container to propogate out.
      $('#cart_notification').once('bind-events').on('click', function (event) {
        event.stopPropagation();
      });

      // Create a new instance of ladda for the specified button
      $('.edit-add-to-cart', context).attr('data-style', 'zoom-in');
      var l = $('.edit-add-to-cart').ladda();

      $('.edit-add-to-cart', context).on('click', function () {
        // Start loading
        l.ladda('start');
      });

      $('.edit-add-to-cart', context).on('mousedown', function () {
        // Start loading
        l.ladda('start');
      });

      $('.edit-add-to-cart', context).on('keydown', function (event) {
        if (event.keyCode === 13 || event.keyCode === 32) {
          // Start loading
          l.ladda('start');
        }
      });

      $('[data-drupal-selector="edit-configurables-size"]', context).once('bind-events').on('change', function () {
        // Start loading.
        // $(this).closest('#configurable_ajax').siblings('.ladda-button').ladda( 'start' );
        // $(this).parents('form').find('.ladda-button').ladda('start')
        l.ladda('start');
      });

      $(document).ajaxComplete(function (event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) && (settings.extraData._triggering_element_name === 'configurables[size]')) {
          $(this).stopSpinner(['success']);
        }
        else if (!settings.hasOwnProperty('extraData') && l.ladda('isLoading')) {
          $.ladda('stopAll');
        }
      });

      $.fn.cartNotificationScroll = function () {
        $('html,body').animate({
          scrollTop: $('.header--wrapper').offset().top
        }, 'slow');
      };

      $.fn.stopSpinner = function (data) {
        l.ladda('stop');
        if (data.message === 'success') {
          $('.edit-add-to-cart').find('.ladda-label').html(Drupal.t('added'));
          if ($('.ui-dailog')) {
            $('.ui-dialog .ui-dialog-titlebar-close').trigger('click');
          }
        }
        else if (data.message === 'failure') {
          $('.edit-add-to-cart').find('.ladda-label').html(Drupal.t('error'));
        }
        setTimeout(
          function () {
            $('.edit-add-to-cart').find('.ladda-label').html(Drupal.t('add to cart'));
          }, data.interval);
      };
    }
  };

})(jQuery, Drupal, document);
