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
          var promotionLabel = '.acq-content-product .promotions .promotions-dynamic-label.sku-' + dynamicPromotionSku;
          // Register the custom trigger event on element.
          $.fn.showDynamicPromotionLabel(promotionLabel);
          var getPromoLabel = new Drupal.ajax({
            url: Drupal.url('get-promotion-dynamic-label/' + dynamicPromotionSku),
            element: false,
            base: false,
            progress: {type: 'throbber'},
            submit: {js: true},
            success: Drupal.triggerShowDynamicPromotionLabel(context, dynamicPromotionSku)
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
        // Go ahead and display dynamic promotions.
        $('.acq-content-product .content__title_wrapper .promotions .promotions-dynamic-label', context).once('update-promo-label-pdp').each(function () {
          updateAlshayaPromotionsLabel(alshayaAcmPromotions, context);
        });
      }
    }
  };

  // Trigger dynamic:promotion:label:ajax:complete on promotions-dynamic-label.
  Drupal.triggerShowDynamicPromotionLabel = function (context, dynamicPromotionSku) {
    var promotionLabel = '.promotions-dynamic-label.sku-' + dynamicPromotionSku;
    $(promotionLabel, context).trigger('dynamic:promotion:label:ajax:complete');
  };

  // Reveal the Dynamic Promotion Label with a slowDown.
  $.fn.showDynamicPromotionLabel = function(data) {
    // Slide down the dynamic label.
    $(data).on('cart:notification:animation:complete dynamic:promotion:label:ajax:complete', function() {
      $(this).slideDown('slow');
    });
  };

})(jQuery, Drupal, drupalSettings);
