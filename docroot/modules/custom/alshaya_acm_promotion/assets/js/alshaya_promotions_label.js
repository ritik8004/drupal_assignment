/**
 * @file
 * Alshaya Promotions Label Manager.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  function updateAlshayaPromotionsLabel(currentSku, context) {
    var dynamicPromoLabelHolder = $('.acq-content-product .promotions div.promotions-labels', context);
    var cartQuantity = $('#block-cartminiblock #mini-cart-wrapper span.quantity', context);

    // Check if cart is empty.
    if (cartQuantity.length) {
      if (currentSku  !== undefined) {
        var getPromoLabel = new Drupal.ajax({
          url: Drupal.url('get-promotion-label/' + currentSku),
          element: false,
          base: false,
          progress: {type: 'throbber'},
          submit: {js:true}
        });

        getPromoLabel.options.type = 'GET';
        getPromoLabel.execute();
      }
    }
    else {
      dynamicPromoLabelHolder.removeClass('hidden');
    }
  }

  Drupal.behaviors.alshayaPromotionsLabelManager = {
    attach: function (context) {
      var alshayaAcmPromotions = drupalSettings.alshayaAcmPromotions;

      if (alshayaAcmPromotions !== undefined) {
        // Go ahead and display dynamic promotions.
        var currentSku = alshayaAcmPromotions.currentSku;
        $('.acq-content-product .promotions div', context).once('update-promo-label-pdp').each(function () {
          updateAlshayaPromotionsLabel(currentSku, context);
        });
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
