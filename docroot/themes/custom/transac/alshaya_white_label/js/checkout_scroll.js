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

      var scrollHeight = $('#shipping_methods_wrapper').offset().top;
      if (localStorage.getItem('address_save_scroll') === 'Y') {
        $('html,body').animate({
          scrollTop: scrollHeight
        }, 'slow');
        localStorage.removeItem('address_save_scroll');
      }

      // Scroll the checkout delivery page to 'Delivery to' section for mobile devices.
      if ($(window).width() < 768) {
        if ($('.multistep-checkout').hasClass('show-form')) {
          if ($('#edit-member-delivery-home').length || $('#edit-guest-delivery-home').length) {
            $('html, body').animate({
              scrollTop: $('#edit-member-delivery-home, #edit-guest-delivery-home').offset().top
            }, 'slow');
          }
        }
      }
    }
  };

})(jQuery, Drupal);
