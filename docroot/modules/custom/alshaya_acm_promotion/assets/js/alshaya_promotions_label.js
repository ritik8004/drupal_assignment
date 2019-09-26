/**
 * @file
 * Alshaya Promotions Label Manager.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  function updateAlshayaPromotionsLabel(alshayaAcmPromotions, context) {
    for (var dynamicPromotionSku in alshayaAcmPromotions) {
      if (alshayaAcmPromotions.hasOwnProperty(dynamicPromotionSku)) {
        var cartQuantity = $('#block-cartminiblock #mini-cart-wrapper span.quantity', context);

        // If cart is not empty.
        if (cartQuantity.length) {
          var getPromoLabel = new Drupal.ajax({
            url: Drupal.url('get-promotion-dynamic-label/' + dynamicPromotionSku),
            element: false,
            base: false,
            progress: {type: 'throbber'},
            submit: {js: true}
          });

          getPromoLabel.options.type = 'GET';
          getPromoLabel.execute();
        }
      }
    }
  }

  Drupal.behaviors.alshayaPromotionsLabelManager = {
    attach: function (context) {
      var alshayaAcmPromotions = drupalSettings.alshayaAcmPromotions;

      if (alshayaAcmPromotions !== undefined) {
        // Slide down the dynamic label.
        $('.promotions-dynamic-label', context).on('cart:notification:animation:complete dynamic:promotion:label:ajax:complete', function() {
          $(this).slideDown('slow');
        });
        // Go ahead and display dynamic promotions.
        $('.acq-content-product .content__title_wrapper .promotions .promotions-dynamic-label', context).once('update-promo-label-pdp').each(function () {
          updateAlshayaPromotionsLabel(alshayaAcmPromotions, context);
        });
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
