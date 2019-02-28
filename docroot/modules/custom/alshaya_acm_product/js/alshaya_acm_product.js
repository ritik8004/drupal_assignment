/**
 * @file
 */

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
            if ($('.magazine-layout').length > 0 && $('.pdp-modal-overlay').length < 1) {
              $('.content__main #product-zoom-container').replaceWith($(this));
              Drupal.behaviors.magazine_gallery.attach($(this), settings);
            }
            else {
              // Execute the attach function of alshaya_product_zoom again.
              $(this).closest('.content__sidebar').siblings('.content__main').find('#product-zoom-container').replaceWith($(this));
              Drupal.behaviors.alshaya_product_zoom.attach($(this), settings);
            }
          }
          else {
            $(this).remove();
          }
        });
      }

      // For modal windows we do in attach.
      $('[data-vmode="modal"] .sku-base-form').removeClass('visually-hidden');
    }
  };

  $(window).on('load', function () {
    // Show add to cart form now.
    $('.sku-base-form').removeClass('visually-hidden');
    if ($('.magazine-layout').length > 0 || $(window).width() < 768) {
      $('.content__title_wrapper').addClass('show-sticky-wrapper');
    }
  });

  $.fn.replaceDynamicParts = function (data) {
    if (data.replaceWith === '') {
      // Do nothing.
    }
    else {
      $(data.selector).replaceWith(data.replaceWith);

      // We trigger focus of cart button to avoid issue in iOS.
      setTimeout(function () {
        jQuery('.mobile-content-wrapper .edit-add-to-cart', $(data.selector).closest('form')).trigger('focus');
      }, 50);
    }
  };
})(jQuery, Drupal);
