/**
 * @file
 * Alshaya Promotions Label Manager.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  function updateAlshayaPromotionsLabel(alshayaAcmPromotions, context) {
    for (var dynamicPromotionSku in alshayaAcmPromotions) {
      if (alshayaAcmPromotions.hasOwnProperty(dynamicPromotionSku)) {
        var dynamicPromoLabelHolders = $('.acq-content-product .promotions .promotions-labels', context);
        var cartQuantity = $('#block-cartminiblock #mini-cart-wrapper span.quantity', context);

        // If cart is not empty.
        if (cartQuantity.length) {
          var getPromoLabel = new Drupal.ajax({
            url: Drupal.url('get-promotion-label/' + dynamicPromotionSku),
            element: false,
            base: false,
            progress: {type: 'throbber'},
            submit: {js: true}
          });

          getPromoLabel.options.type = 'GET';
          getPromoLabel.execute();
        }
        else {
          dynamicPromoLabelHolders.removeClass('hidden');
        }
      }
    }
  }

  Drupal.behaviors.alshayaPromotionsLabelManager = {
    attach: function (context) {
      var alshayaAcmPromotions = drupalSettings.alshayaAcmPromotions;

      if (alshayaAcmPromotions !== undefined) {
        // Go ahead and display dynamic promotions.
        $('.acq-content-product .content__title_wrapper .promotions div', context).once('update-promo-label-pdp').each(function () {
          updateAlshayaPromotionsLabel(alshayaAcmPromotions, context);
        });
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
