(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for product page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for product page.
   */
  Drupal.behaviors.alshaya_acm_product = {
    attach: function (context, settings) {
      // If we find the gallery in add cart form ajax response, we update the main gallery.
      if ($('.field--name-field-skus #product-zoom-container').length > 0) {
        $('.field--name-field-skus #product-zoom-container').each(function () {
          if ($(this).closest('td.sell-sku').length === 0) {
            // Execute the attach function of alshaya_product_zoom again.
            Drupal.behaviors.alshaya_product_zoom.attach($(this), settings);
            $(this).closest('.content__sidebar').siblings('.content__main').find('#product-zoom-container').replaceWith($(this));
          }
          else {
            $(this).remove();
          }
        });
      }
    }
  };

  $.fn.replaceDynamicParts = function (data) {
    if (data.replaceWith === '') {
      // Do nothing
    }
    else {
      $(data.selector).replaceWith(data.replaceWith);

      // We trigger focus of cart button to avoid issue in iOS.
      setTimeout(function () {
        jQuery('.edit-add-to-cart', $(data.selector).closest('form')).trigger('focus');
      }, 50);
    }
  };

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
    if (windowBottom >= mobileCWBottom) {
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
        $(window, context).on('scroll', function () {
          mobileStickyAddtobasketButton();
        });
      }
    }
  };
})(jQuery, Drupal);
