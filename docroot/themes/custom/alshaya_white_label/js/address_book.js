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
        $('body').addClass('modal-overlay');

        $(document).ajaxComplete(function () {
          toggleOverlay('.ui-dialog-titlebar-close', 'modal-overlay');
          toggleOverlay('.ui-dialog-buttonpane .dialog-cancel', 'modal-overlay');
        });
      });

      $('#edit-field-address-0-address-administrative-area').select2();
    }
  };

})(jQuery, Drupal);
