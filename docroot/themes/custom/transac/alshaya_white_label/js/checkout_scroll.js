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

      var counter;
      $('.store-actions .select-store').once().on('click', function () {
        counter = 0;
        $(document).ajaxComplete(function () {
          if (counter === 0) {
            $('html,body').animate({
              scrollTop: $('#selected-store-wrapper').offset().top
            }, 'slow');
            counter++;
          }
        });
      });

      $('.address--deliver-to-this-address .use-ajax').on('click', function () {
        $(document).ajaxComplete(function () {
          localStorage.setItem('address_save_scroll', 'Y');
        });
      });

      // Scroll the checkout delivery page to 'Delivery option' section.
      var shippingMethodsWrapper = $('#shipping_methods_wrapper');
      if (localStorage.getItem('address_save_scroll') === 'Y' && shippingMethodsWrapper.length) {
        $('html,body').animate({
          scrollTop: shippingMethodsWrapper.offset().top
        }, 'slow');
        localStorage.removeItem('address_save_scroll');
      }
      else {
        // Scroll the checkout delivery page to 'Delivery to' section.
        if ($('.multistep-checkout').hasClass('show-form')) {
          var selectedTab = $('.multistep-checkout').find('input#selected-tab').val();
          if (selectedTab === 'checkout-home-delivery') {
            if ($('#edit-member-delivery-home').length || $('#edit-guest-delivery-home').length) {
              $('html, body').animate({
                scrollTop: $('#edit-member-delivery-home, #edit-guest-delivery-home').offset().top
              }, 'slow');
            }
          }
        }
      }
    }
  };

})(jQuery, Drupal);
