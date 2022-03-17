/**
 * @file
 * Alshaya RCS Promotions Label Manager.
 */

// @todo Split this file into two, One containing all common code and one
// containing the specific functions.
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

  /**
   * Refresh the dynamic promotion label after cart update.
   *
   * @param {string} sku
   *   The SKU of the product.
   * @param {object} cartData
   *   The cart data object.
   */
  Drupal.alshayaPromotions.refreshDynamicLabels = function (sku, cartData) {
    const response = Drupal.alshayaPromotions.getRcsDynamicLabel(sku, cartData);
    if (response && response.label) {
      Drupal.alshayaPromotions.updateDynamicLabel(sku, response);
    }
  };

  /**
   * Display the dynamic lable of the product.
   *
   * @param {string} sku
   *   The SKU value of the product.
   */
  Drupal.alshayaPromotions.displayDynamicLabels = function (sku) {
    var cartData = Drupal.alshayaSpc.getCartData();
    if (!cartData) {
      return;
    }

    Drupal.alshayaPromotions.refreshDynamicLabels(sku, cartData);
  };

  /**
   * Update the dynamic promotion label of the product.
   *
   * @param {string} sku
   *   The SKU value of the product.
   * @param {object} response
   *   The response object containing the dynamic promotion label.
   */
  Drupal.alshayaPromotions.updateDynamicLabel = function (sku, response) {
    // If label info available in response.
    if (response.label !== undefined && response.label !== null) {
      $('[data-sku="' + sku + '"]').find('.promotions-dynamic-label').html(response.label);
      $('[data-sku="' + sku + '"]').find('.promotions-dynamic-label').trigger('cart:notification:animation:complete');
    }
  };

  /**
   * Call Magento graphql endpoint to get Dynamic Promotion info.
   *
   * @param {string} sku
   *   The SKU value of the product.
   * @param {object} cartData
   *   The object containing the current cart data.
   * @param {string} viewMode
   *   The type of response we expect from magento.
   * @param {string} type
   *   The type of dynamic label ( Product or cart ).
   *
   * @returns {object}
   *   An oject containing the dynamic promotion label.
   */
  Drupal.alshayaPromotions.getRcsDynamicLabel = function (sku, cartData, viewMode = 'links', type = 'product') {
    let response = null;
    if (typeof drupalSettings.alshayaRcs !== 'undefined') {
      // Prepare cart object.
      let cartInfo = [];
      // Define sku and view_mode based on the request type.
      let queryProductSku = '',
      queryProductViewMode = '',
      queryCartAttr = '';
      if (type === 'product') {
        queryProductSku = `sku: "${sku}"`;
        queryProductViewMode = `view_mode: "${viewMode}"`;
      }
      else if (type === 'cart') {
        queryCartAttr = `
          subtotal: ${cartData.totals.subtotal_incl_tax}
          applied_rules: "${cartData.appliedRules}"`;
      }
      for (const key in cartData.items) {
        cartInfo.push(`{
            sku: "${cartData.items[key].sku}",
            qty: ${parseInt(cartData.items[key].qty)}
            ${
              // Conditionaly adding the price attribute in query because it's
              // required for cart Graphql query only and this cannot be passed
              // as an extra attribute for product dynamic promotion query.
              type === 'cart' ? 'price: ' + cartData.items[key].price : ''
            }
          }`);
      }

      // Change the query type and body based on the type of the request.
      let queryType = 'promoDynamicLabelProduct';
      let queryBody = rcsPhGraphqlQuery.product_dynamic_promotions.query;
      if (type === 'cart') {
        queryType = 'promoDynamicLabelCart';
        queryBody = rcsPhGraphqlQuery.cart_dynamic_promotions.query;
      }

      response = globalThis.rcsPhCommerceBackend.getDataSynchronous('dynamic-promotion-label', {
        queryType,
        queryProductSku,
        queryProductViewMode,
        queryCartAttr,
        cartInfo,
        queryBody,
      });
      // Update the response variable based on querytype.
      response = response.data[queryType];
    }

    return response;
  }

})(jQuery, Drupal, drupalSettings);
