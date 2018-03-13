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

      $(window).on('resize', function (e) {
        if ($(window).width() > 768) {
          $('.back-link').click(function (event) {
            event.preventDefault();
          });
        }
      });
    }
  };

  Drupal.behaviors.checkoutScroll = {
    attach: function (context, settings) {
      $(document).ajaxComplete(function (event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) &&
            (settings.extraData._triggering_element_name === 'op')) {
          localStorage.setItem('address_save_scroll', 'Y');
        }
      });

      var guestDiv = $('#edit-guest-delivery-home-address-shipping-methods');
      var memberDiv = $('#edit-member-delivery-home-address-shipping-methods');
      var scrollHeight = (memberDiv.length > 0) ? memberDiv.offset().top : guestDiv.offset().top;
      if (localStorage.getItem('address_save_scroll') === 'Y') {
        $('html,body').animate({
          scrollTop: scrollHeight
        }, 'slow');
        localStorage.removeItem('address_save_scroll');
      }
    }
  };

})(jQuery, Drupal);
