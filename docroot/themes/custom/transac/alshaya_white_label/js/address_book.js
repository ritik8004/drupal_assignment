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

      // On dialog close.
      $(window).on('dialog:afterclose', function (e, dialog, $element) {
        // If body has overlay class, remove it.
        if ($('body').hasClass('modal-overlay')) {
          $('body').removeClass('modal-overlay');
          // We have a menu timer with delay on desktop for body::before
          // transition, also some regions have differnet z-index.
          // This class holds the z-index consisitent till all animations are
          // over. Otherwise we get a step animation, where the opacity for
          // background closes at different times for differnet regions.
          // see _utils.scss for classes where this gets applied.
          setTimeout(function () {
            $('body').removeClass('reduce-zindex');
          }, 550);
        }
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
