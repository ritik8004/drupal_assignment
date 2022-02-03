/**
 * @file
 * Address Book.
 */

(function ($, Drupal) {

  Drupal.behaviors.address = {
    attach: function (context, settings) {
      function toggleOverlay(button, className) {
        $(button).click(function () {
          $('body').removeClass(className);
        });
      }

      $('.address--delete a').click(function () {
        if ($(window).width() < 768) {
          $('body').addClass('mobile--overlay address--delete-popup');

          $(document).ajaxComplete(function () {
            toggleOverlay('.ui-dialog-titlebar-close', 'mobile--overlay address--delete-popup');
            toggleOverlay('.address--delete-popup .dialog-cancel', 'mobile--overlay address--delete-popup');
          });
        }

        else {
          $('body').addClass('reduce-zindex modal-overlay');

          $(document).ajaxComplete(function () {
            toggleOverlay('.ui-dialog-titlebar-close', 'modal-overlay');
            toggleOverlay('.ui-dialog-buttonpane .dialog-cancel', 'modal-overlay');
          });
        }
      });
    }
  };

})(jQuery, Drupal);
