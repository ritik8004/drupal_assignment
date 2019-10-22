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
          $(this).slideDown('slow');
          if ($(window).width() < 768 && $('.nodetype--acq_product').length > 0) {
            Drupal.alshayaPromotions.stickyDynamicPromotionLabel();
          }
        });

        // Go ahead and display dynamic promotions.
        $('.acq-content-product .content__title_wrapper .promotions .promotions-dynamic-label', context).once('update-promo-label-pdp').each(function () {
          updateAlshayaPromotionsLabel(alshayaAcmPromotions);

          if ($(window).width() < 768) {
            $('.acq-content-product .content__title_wrapper').addClass('dynamic-promotion-wrapper');
          }
        });

        // Bind event to calculate the height of dynamic promo position and make it scrollable or sticky accordingly.
        $('.promotions-dynamic-label').once('bind-alshaya-acm-product-detail-thumbnails-loaded').on('alshaya-acm-product-detail-thumbnails-loaded', function () {
          Drupal.alshayaPromotions.stickyDynamicPromotionLabel();
        });
      }

    }
  };

  Drupal.alshayaPromotions = Drupal.alshayaPromotions || {};

  /**
   * Js to make the scrollable dynamic promotion depend on the image height.
   *
   */
  Drupal.alshayaPromotions.stickyDynamicPromotionLabel = function () {
    var dynamicPromotionPosition = $('.promotions-dynamic-label').offset().top;

    // Scroll required to make it sticky with image.
    // 56 is the fixed height for scrollable sticky add to cart button and value is fixed for all the brands.
    var dynamicPromotionScrollHeight = $('.content__title_wrapper').offset().top - $(window).height() + 56;

    // If image height is more than screen height make promo sticky on load.
    if ($(window).height() < dynamicPromotionPosition) {
      $('.acq-content-product .content__title_wrapper').addClass('sticky-promotion-wrapper');
    }

    $(window).once('dynamicPromotion').on('scroll', function () {
      // Make it scrollable till main image end point is not visible in mobile view port.
      if ($(this).scrollTop() < dynamicPromotionScrollHeight) {
        $('.acq-content-product .content__title_wrapper').addClass('sticky-promotion-wrapper');
      }
      else {
        $('.acq-content-product .content__title_wrapper').removeClass('sticky-promotion-wrapper');
      }
    });
  };

})(jQuery, Drupal, drupalSettings);
