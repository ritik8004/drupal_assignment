/**
 * @file
 * Alshaya Promotions Label Manager.
 */

(function ($, Drupal) {
  'use strict';

  function updateAlshayaPromotionsLabel(context) {
    var dynamicPromoLabelHolder = $('.acq-content-product .promotions .dynamic-promo-label-ajax', context);
    var cartQuantity = $('#block-cartminiblock #mini-cart-wrapper span.quantity', context);

    // Check if cart is empty.
    if (cartQuantity.length) {
      var currentSku = dynamicPromoLabelHolder.attr('data-sku');
      if (currentSku  !== undefined) {
        Drupal.ajax({url: '/ajax/get-promotion-label/' + currentSku}).execute();
      }
    }
    else {
      dynamicPromoLabelHolder.removeClass('hidden');
    }
  }

  Drupal.behaviors.alshayaPromotionsLabelManager = {
    attach: function (context) {
      $('.acq-content-product .promotions .dynamic-promo-label-ajax', context).once('update-promo-label-pdp').each(function (context) {
        updateAlshayaPromotionsLabel(context);

        $(document, context).once('update-promo-label-after-add-cart').ajaxComplete(function( event, xhr, settings, context ) {
          var addToCartUrl = new RegExp("add-to-cart-submit\\/\\d*");
          if (addToCartUrl.test(settings.url)) {
            updateAlshayaPromotionsLabel(context);
          }
        });
      });

    }
  };

})(jQuery, Drupal);
