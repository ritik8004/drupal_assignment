/**
 * @file
 * PDP sticky button js file.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Helper function to compute height of add to cart button and make it sticky.
   * @param {String} direction The scroll direction
   */
  function mobileStickyAddtobasketButton(direction) {
    // Button top.
    var button = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper .edit-add-to-cart');
    // This is the wrapper that holds delivery options.
    var mobileContentWrapper = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper');
    // mobileContentWrapper bottom, based on direction we have to factor in the height of button if it is already fixed,
    // 4 is the offset to smooth the toggle from fixed to scroll.
    var mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height();
    if (direction === 'up') {
      mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height() + button.outerHeight();
    }

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
              mobileContentWrapper.css('height', mobileContentWrapper.height() + buttonHeight - 8);
              observer.disconnect();
            }
          });
        };
        // Create an observer instance linked to the callback function
        var observer = new MutationObserver(callback);
        // Start observing the target node for configured mutations
        observer.observe(targetNode, config);

        mobileStickyAddtobasketButton('bottom');
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
          mobileStickyAddtobasketButton(direction);
        });
      }
    }
  };

})(jQuery, Drupal);


