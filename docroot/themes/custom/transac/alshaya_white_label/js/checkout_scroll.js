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

      // Scroll the checkout delivery page to 'Delivery option' section.
      var shippingMethodsWrapperLength = $('#shipping_methods_wrapper').length;
      if (localStorage.getItem('address_save_scroll') === 'Y' && shippingMethodsWrapperLength) {
        $('html,body').animate({
          scrollTop: shippingMethodsWrapperLength.offset().top
        }, 'slow');
        localStorage.removeItem('address_save_scroll');
      }
      else {
        // Scroll the checkout delivery page to 'Delivery to' section.
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
