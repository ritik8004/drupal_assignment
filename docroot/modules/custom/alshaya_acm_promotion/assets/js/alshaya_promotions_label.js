/**
 * @file
 * Alshaya Promotions Label Manager.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshayaPromotionsLabelManager = {
    attach: function (context) {
      Drupal.alshayaPromotions.initializeDynamicPromotions(context);
      // Update promotion label on product-add-to-cart-success.
      $('.sku-base-form', context).once('js-process-promo-label').on('product-add-to-cart-success', function (event) {
        var productData = event.detail.productData;
        var cartData = event.detail.cartData;
        if (productData && cartData) {
          Drupal.alshayaPromotions.refreshDynamicLabels(productData.sku, cartData);
        }
      });
    }
  };

  Drupal.alshayaPromotions = Drupal.alshayaPromotions || {};

  /**
   * Js to make the scrollable dynamic promotion depend on the position inside
   * view port.
   */
  Drupal.alshayaPromotions.stickyDynamicPromotionLabel = function () {
    $('.content__sidebar').addClass('dynamic-promotions-wrapper');
    // Adding an event to recalculate the pdp sidebar container.
    $('.basic-details-wrapper').trigger('pdp-dynamic-promotion-enabled');

    $(window).once('dynamicPromotion').on('scroll', function () {
      // Remove the specific class added for slide the dynamic promotion.
      if ($('.basic-details-wrapper').hasClass('slide-dynamic-promotion-button')) {
        $('.basic-details-wrapper').removeClass(('slide-dynamic-promotion-button'));
      }

      if ($('.edit-add-to-cart').hasClass('fix-button')) {
        $('.basic-details-wrapper').addClass('fix-dynamic-promotion-button');
      }
      else {
        $('.basic-details-wrapper').removeClass('fix-dynamic-promotion-button');
      }
    });
  };

  /**
   * Js to initialize dynamic promotion labels.
   * @param context
   */
  Drupal.alshayaPromotions.initializeDynamicPromotions = function () {
    // Slide down the dynamic label.
    $('.promotions-dynamic-label').once('initializeDynamicPromotions').each(function () {
      $(this).on('cart:notification:animation:complete', function () {
        if ($(window).width() > 767) {
          $(this).slideDown('slow', function () {
          });
        }
        else if ($(window).width() < 768 && $('.nodetype--acq_product').length > 0) {
          if ($('.edit-add-to-cart').hasClass('fix-button')) {
            // Add the specific class added for slide the dynamic promotion.
            $('.basic-details-wrapper').addClass('fix-dynamic-promotion-button slide-dynamic-promotion-button');
          }
          else {
            $('.basic-details-wrapper').removeClass('fix-dynamic-promotion-button');
          }
          Drupal.alshayaPromotions.stickyDynamicPromotionLabel();
          $('.promotions-dynamic-label').removeClass('mobile-only-dynamic-promotion');
        }
      });

      var sku = $(this).parents('[data-sku]:first').attr('data-sku');
      Drupal.alshayaPromotions.displayDynamicLabels(sku);
    });

    // Cut the Dynamic promotion wrapper and insert it after add to cart button.
    if ($(window).width() < 768) {
      if ($('.promotions .sku-dynamic-promotion-link').length > 0) {
        var dynamicPromotionWrapper = $('.promotions .promotions-dynamic-label').clone();
        if ($('.basic-details-wrapper .promotions-dynamic-label').length < 1) {
          dynamicPromotionWrapper.once('bind-promotions-dynamic-label-events').insertAfter($('.edit-add-to-cart'));
        }
        else {
          // Replace the same promotion wrapper with updated dynamic label.
          $('.basic-details-wrapper .promotions-dynamic-label').replaceWith(dynamicPromotionWrapper);
        }
      }
      else {
        $('.basic-details-wrapper .promotions-dynamic-label').remove();
      }
    }
  };

  Drupal.alshayaPromotions.refreshDynamicLabels = function (sku, cartData) {
    var cartDataUrl = Drupal.alshayaSpc.getCartDataAsUrlQueryString(cartData);
    // We set cacheable=1 so it is always treated as anonymous user request.
    jQuery.ajax({
      url: Drupal.url('promotions/dynamic-label-product/' + sku) + '?cacheable=1&context=web&' + cartDataUrl,
      method: 'GET',
      async: true,
      success: function (response) {
        Drupal.alshayaPromotions.updateDynamicLabel(sku, response);
      }
    });
  };

  Drupal.alshayaPromotions.displayDynamicLabels = function (sku) {
    var cartData = Drupal.alshayaSpc.getCartData();
    if (!cartData) {
      return;
    }

    Drupal.alshayaPromotions.refreshDynamicLabels(sku, cartData);
  };

  Drupal.alshayaPromotions.updateDynamicLabel = function (sku, response) {
    // If label info available in response.
    if (response.label !== undefined && response.label !== null) {
      $('[data-sku="' + sku + '"]').find('.promotions-dynamic-label').html(response.label);
      $('[data-sku="' + sku + '"]').find('.promotions-dynamic-label').trigger('cart:notification:animation:complete');
    }
  };

})(jQuery, Drupal, drupalSettings);
