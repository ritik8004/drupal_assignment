/**
 * @file
 * PDP sticky button js file.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Helper function to compute height of add to cart button and make it sticky.
   * @param {String} direction The scroll direction
   *
   * @param {string} state The moment when function is called, initial/after.
   */
  function mobileStickyAddtobasketButton(direction, state) {
    // Add to cart button.
    var button = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper .edit-add-to-cart');
    // This is the wrapper that holds delivery options.
    var mobileContentWrapper = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper');
    var windowBottom;
    var mobileCWBottom;
    var mobileDynamicWrapper = 0;

    // Only if dynamic promotion is enabled on pdp.
    if ($('.sku-dynamic-promotion-link').length > 0) {
      mobileDynamicWrapper = $('.promotions-dynamic-label').height() - 10;
    }
    if (state === 'initial') {
      // Button top.
      var buttonTop = mobileContentWrapper.offset().top + mobileContentWrapper.height();
      // Screen bottom.
      windowBottom = $(window).scrollTop() + $(window).height();
      if (buttonTop > windowBottom) {
        button.addClass('fix-button');
        if ($('.sku-dynamic-promotion-link').length > 0) {
          // Add the specific class added for slide the dynamic promotion on load.
          $('.basic-details-wrapper').addClass('fix-dynamic-promotion-button slide-dynamic-promotion-button');
        }
      }
      else {
        button.removeClass('fix-button');
      }
      return;
    }
    else {
      // mobileContentWrapper bottom, based on direction we have to factor in the height of button
      // if it is already fixed.
      mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height() + 8;
      if (direction === 'up') {
        mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height() + button.outerHeight() + mobileDynamicWrapper - 60;
      }

      // Screen scroll offset.
      windowBottom = $(window).scrollTop() + $(window).height();
      // Hide button when we are below delivery wrapper.
      if (windowBottom > mobileCWBottom && mobileContentWrapper.length) {
        button.removeClass('fix-button');
      }
      else {
        button.addClass('fix-button');
      }
    }
  }

  /**
   * Helper function to compute height calculation of attributes wrapper.
   */
  function cartFormHeightCalculation() {
    var buttonHeight = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper .edit-add-to-cart').outerHeight();
    var mobileContentWrapper = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper');
    var mobileDynamicWrapper = 0;

    // Only if dynamic promotion is enabled on pdp.
    if ($('.sku-dynamic-promotion-link').length > 0) {
      mobileDynamicWrapper = $('.promotions-dynamic-label').height();
    }
    mobileContentWrapper.css('height', 'auto');
    mobileContentWrapper.css('height', mobileContentWrapper.height() + buttonHeight + mobileDynamicWrapper - 8);
  }

  /**
   * Helper function to compute height calculation of attributes wrapper on cart form load.
   */
  function mobileStickyAddtobasketButtonHeightCalculation() {
    // Only on mobile.
    if ($(window).width() < 768) {
      // Calculate the height of attributes wrapper.
      cartFormHeightCalculation();

      // Once add to cart form loaded recalculate the height of attributes wrapper.
      $('.sku-base-form').once('bind-form-visible').on('form-visible', function () {
        cartFormHeightCalculation();
        mobileStickyAddtobasketButton('bottom', 'initial');
      });

      var lastScrollTop = 0;
      $(window).on('scroll', function () {
        var windowScrollTop = $(this).scrollTop();
        var direction = 'bottom';
        if (windowScrollTop > lastScrollTop) {
          direction = 'bottom';
        }
        else {
          direction = 'up';
        }
        lastScrollTop = windowScrollTop;
        mobileStickyAddtobasketButton(direction, 'after');
      });
    }
  }

  mobileStickyAddtobasketButtonHeightCalculation();

  Drupal.behaviors.stickyAddtobasketButton = {
    attach: function (context, settings) {
      $('.basic-details-wrapper').once('bind-pdp-dynamic-promotion-enabled').on('pdp-dynamic-promotion-enabled', function () {
        mobileStickyAddtobasketButtonHeightCalculation();
      });
    }
  };

})(jQuery, Drupal);
