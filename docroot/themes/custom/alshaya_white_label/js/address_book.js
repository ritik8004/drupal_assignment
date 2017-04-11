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
          $('body').toggleClass(className);
        });
      }

      $('.address--delete a').click(function () {
        $('body').addClass('modal-overlay');

        setTimeout(function () {
          toggleOverlay('.ui-dialog-titlebar-close', 'modal-overlay');
        }, 5000);
      });


    }
  };

})(jQuery, Drupal);
