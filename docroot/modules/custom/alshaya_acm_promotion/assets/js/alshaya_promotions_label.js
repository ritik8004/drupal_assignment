/**
 * @file
 * Alshaya Promotions Label Manager.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  function updateAlshayaPromotionsLabel(alshayaAcmPromotions) {
    for (var dynamicPromotionSku in alshayaAcmPromotions) {
      if (alshayaAcmPromotions.hasOwnProperty(dynamicPromotionSku)) {
        var cartQuantity = $('#block-cartminiblock #mini-cart-wrapper span.quantity');

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
          $(this).slideDown('slow', function() {
            if ($(window).width() < 768 && $('.nodetype--acq_product').length > 0) {
              Drupal.alshayaPromotions.stickyDynamicPromotionLabel();
              // Adding an event to recalculate the pdp sidebar container.
              $('.basic-details-wrapper').trigger('pdp-dynamic-promotion-enabled');
            }
          });
        });

        // Go ahead and display dynamic promotions.
        $('.acq-content-product .content__title_wrapper .promotions .promotions-dynamic-label', context).once('update-promo-label-pdp').each(function () {
          updateAlshayaPromotionsLabel(alshayaAcmPromotions);
        });
      }

    }
  };

  Drupal.alshayaPromotions = Drupal.alshayaPromotions || {};

  /**
   * Js to make the scrollable dynamic promotion depend on the position inside view port.
   *
   */
  Drupal.alshayaPromotions.stickyDynamicPromotionLabel = function () {
    var dynamicPromotionWrapper = $('.promotions .promotions-dynamic-label').clone();
    dynamicPromotionWrapper.once('bind-promotions-dynamic-label-events').insertAfter($('.edit-add-to-cart'));
    $('.promotions .promotions-dynamic-label').remove();
    $('.content__sidebar').addClass('dynamic-promotions-wrapper');

    if ($('.edit-add-to-cart').hasClass('fix-button')) {
      $('.basic-details-wrapper').addClass('fix-dynamic-promotion-button');
    }
    else {
      $('.basic-details-wrapper').removeClass('fix-dynamic-promotion-button');
    }

    $(window).once('dynamicPromotion').on('scroll', function () {
      if ($('.edit-add-to-cart').hasClass('fix-button')) {
        $('.basic-details-wrapper').addClass('fix-dynamic-promotion-button');
      }
      else {
        $('.basic-details-wrapper').removeClass('fix-dynamic-promotion-button');
      }
    });
  };

})(jQuery, Drupal, drupalSettings);
