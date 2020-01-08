/**
 * @file
 * Footer space filler JS.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.footerSpaceFiller = {
    attach: function (context, settings) {
      // Blacklisted pages.
      if ($('.page-standard').hasClass('disable-footerspace-fill')) {
        return false;
      }

      // Check if we have empty space below the footer,
      // Add that much space above it so that footer is touches the screen bottom.
      var checkoutFooter = false;
      var footerBottom;
      var difference;
      if ($('body').hasClass('alias--cart-checkout-login') || $('body').hasClass('alias--cart-checkout-delivery')
       || $('body').hasClass('alias--cart-checkout-payment') || $('body').hasClass('alias--cart-checkout-confirmation')
       || $('body').hasClass('alias--checkout')) {
        checkoutFooter = true;
      }

      $(window).on('load', function () {
        // Check viewport height.
        var windowHeight = $(window).height();
        // Normal Page.
        if (!checkoutFooter) {
          footerBottom = $('footer').position().top + $('footer').outerHeight();
          if (windowHeight > footerBottom) {
            difference = windowHeight - footerBottom;
            $('footer').addClass('auto-margin-processed');
            $('footer').css('margin-top', difference + 'px');
          }
        }
        // Checkout Page.
        else {
          // On Checkout page, the footer is actually post content + footer secondary.
          footerBottom = $('.c-post-content').position().top + $('.c-post-content').outerHeight()
            + $('.c-footer-secondary').outerHeight();
          if (windowHeight > footerBottom) {
            difference = windowHeight - footerBottom;
            $('.c-post-content').addClass('auto-margin-processed');
            $('.c-post-content').css('margin-top', difference + 'px');
          }
        }
        return;
      });
    }
  };

})(jQuery, Drupal);
