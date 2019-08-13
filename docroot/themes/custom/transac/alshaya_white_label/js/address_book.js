/**
 * @file
 * Address Book.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.address = {
    attach: function (context, settings) {

      function toggleOverlay(button, className) {
        $(button).click(function () {
          $('body').removeClass(className);
        });
      }

      $('.address--delete a').click(function () {
        $('body').addClass('reduce-zindex');
        $('body').addClass('modal-overlay');

        $(document).ajaxComplete(function () {
          toggleOverlay('.ui-dialog-titlebar-close', 'modal-overlay');
          toggleOverlay('.ui-dialog-buttonpane .dialog-cancel', 'modal-overlay');
        });
      });

      $(window).on('resize', function (e) {
        if ($(window).width() > 768) {
          $('.back-link').click(function (event) {
            event.preventDefault();
          });
        }
      });
    }
  };

})(jQuery, Drupal);
