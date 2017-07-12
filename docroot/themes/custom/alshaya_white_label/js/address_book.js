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

      // On dialog close.
      $(window).on('dialog:afterclose', function (e, dialog, $element) {
        // If body has overlay class, remove it.
        if ($('body').hasClass('modal-overlay')) {
          $('body').removeClass('modal-overlay');
        }
      });

      // On change password block - inserting the page-title field into the link
      // so that clicking the whole title works.
      if ($(window).width() > 768) {
        $('.back-link').click(function (event) {
          event.preventDefault();
        });
      }
    }
  };

})(jQuery, Drupal);
