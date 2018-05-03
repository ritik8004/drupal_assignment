/**
 * @file
 * Checkout Scroll.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.checkoutScroll = {
    attach: function (context, settings) {
      $(document).ajaxComplete(function (event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) &&
            (settings.extraData._triggering_element_name === 'op')) {
          localStorage.setItem('address_save_scroll', 'Y');
        }
      });

      $('.store-actions .select-store').on('click', function () {
        $(document).ajaxComplete(function () {
          $('html,body').animate({
            scrollTop: $('#selected-store-wrapper').offset().top
          }, 'slow');
        });
      });

      $('.address--deliver-to-this-address .use-ajax').on('click', function () {
        $(document).ajaxComplete(function () {
          localStorage.setItem('address_save_scroll', 'Y');
        });
      });

      var guestDiv = $('#edit-guest-delivery-home');
      var memberDiv = $('#edit-member-delivery-home');
      var scrollHeight;
      if ((memberDiv.length > 0) || (guestDiv.length > 0)) {
        scrollHeight = (memberDiv.length > 0) ? memberDiv.offset().top : guestDiv.offset().top;
      }
      if (localStorage.getItem('address_save_scroll') === 'Y') {
        $('html,body').animate({
          scrollTop: scrollHeight
        }, 'slow');
        localStorage.removeItem('address_save_scroll');
      }
    }
  };

})(jQuery, Drupal);
