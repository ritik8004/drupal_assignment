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
    if (state === 'initial') {
      // Button top.
      var buttonTop = mobileContentWrapper.offset().top + mobileContentWrapper.height();
      // Screen bottom.
      windowBottom = $(window).scrollTop() + $(window).height();
      if (buttonTop > windowBottom) {
        button.addClass('fix-button');
      }
      else {
        button.removeClass('fix-button');
      }
      return;
    }
    else {
      // mobileContentWrapper bottom, based on direction we have to factor in the height of button
      // if it is already fixed.
      mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height();
      if (direction === 'up') {
        mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height() + button.outerHeight() - 60;
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

  Drupal.behaviors.stickyAddtobasketButton = {
    attach: function (context, settings) {
      // Only on mobile.
      if ($(window).width() < 768) {
        // Select the node that will be observed for mutations
        var targetNode = document.querySelector('.acq-content-product .sku-base-form');
        // Options for the observer (which mutations to observe)
        var config = {attributes: true, childList: false, subtree: false};
        // Callback function to execute when mutations are observed
        var callback = function (mutationsList, observer) {
          mutationsList.forEach(function (mutation) {
            if ((mutation.type === 'attributes') &&
                (mutation.attributeName === 'class') &&
                (!mutation.target.classList.contains('visually-hidden'))) {
              var buttonHeight = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper .edit-add-to-cart').outerHeight();
              var mobileContentWrapper = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper');
              mobileContentWrapper.css('height', 'auto');
              mobileContentWrapper.css('height', mobileContentWrapper.height() + buttonHeight - 8);
              observer.disconnect();
            }
          });
        };
        // Create an observer instance linked to the callback function
        var observer = new MutationObserver(callback);
        // Start observing the target node for configured mutations
        observer.observe(targetNode, config);

        mobileStickyAddtobasketButton('bottom', 'initial');
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
  };

})(jQuery, Drupal);
