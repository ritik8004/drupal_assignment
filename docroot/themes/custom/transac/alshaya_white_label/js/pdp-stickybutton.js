/**
 * @file
 * PDP sticky button js file.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Helper function to compute height of add to cart button and make it sticky.
   */
  function mobileStickyAddtobasketButton() {
    // Button top.
    var button = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper .edit-add-to-cart');
    // This is the wrapper that holds delivery options.
    var mobileContentWrapper = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper');
    // Delivery options bottom.
    var mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height();
    // Screen scroll offset.
    var windowBottom = $(window).scrollTop() + $(window).height();

    // Hide button when we are below delivery wrapper.
    if (windowBottom > mobileCWBottom && mobileContentWrapper.length) {
      button.addClass('hide-button');
    }
    else {
      button.removeClass('hide-button');
    }

  }

  Drupal.behaviors.stickyAddtobasketButton = {
    attach: function (context, settings) {
      // Only on mobile.
      if ($(window).width() < 768) {
        mobileStickyAddtobasketButton();
        $(window).on('scroll', function () {
          mobileStickyAddtobasketButton();
        });
      }
    }
  };

})(jQuery, Drupal);


