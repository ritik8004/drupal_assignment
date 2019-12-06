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
          // Check if we already have fetched dynamic label.
          if (drupalSettings.alshayaAcmPromotionslabels !== undefined && drupalSettings.alshayaAcmPromotionslabels[dynamicPromotionSku] !== undefined) {
            var promotionLabelDiv = $('.promotions-dynamic-label.sku-' + dynamicPromotionSku).html(drupalSettings.alshayaAcmPromotionslabels[dynamicPromotionSku]);
            promotionLabelDiv.trigger('dynamic:promotion:label:ajax:complete');
          }
          else {
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
  }

  Drupal.behaviors.alshayaPromotionsLabelManager = {
    attach: function (context) {
      Drupal.alshayaPromotions.initializeDynamicPromotions(context);

      $('form.sku-base-form').on('variant-selected', function (event, variant, code) {
        Drupal.alshayaPromotions.initializeDynamicPromotions(context);
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
  Drupal.alshayaPromotions.initializeDynamicPromotions = function (context) {
    var alshayaAcmPromotions = drupalSettings.alshayaAcmPromotions;

    if (alshayaAcmPromotions !== undefined) {
      // Slide down the dynamic label.
      $('.promotions-dynamic-label', context).on('cart:notification:animation:complete dynamic:promotion:label:ajax:complete', function() {
        if ($(window).width() > 767) {
          $(this).slideDown('slow', function() {
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

        // Set label in alshayaAcmPromotionslabels to have static cache.
        for (var dynamicPromotionSku in alshayaAcmPromotions) {
          if (alshayaAcmPromotions.hasOwnProperty(dynamicPromotionSku)) {
            if (drupalSettings.alshayaAcmPromotionslabels === undefined) {
              drupalSettings.alshayaAcmPromotionslabels = {};
            }
            drupalSettings.alshayaAcmPromotionslabels[dynamicPromotionSku] = this.innerHTML;
          }
        }
      });

      // Cut the Dynamic promotion wrapper and insert it after add to cart button.
      if ($(window).width() < 768) {
        if ($('.promotions .sku-dynamic-promotion-link').length > 0) {
          var dynamicPromotionWrapper = $('.promotions .promotions-dynamic-label').clone();
          if ($('.basic-details-wrapper .promotions-dynamic-label').length < 1) {
            dynamicPromotionWrapper.once('bind-promotions-dynamic-label-events').insertAfter($('.edit-add-to-cart'));
          } else {
            // Replace the same promotion wrapper with updated dynamic label.
            $('.basic-details-wrapper .promotions-dynamic-label').replaceWith(dynamicPromotionWrapper);
          }
        }
        else {
          $('.basic-details-wrapper .promotions-dynamic-label').remove();
        }
      }

      // Go ahead and display dynamic promotions.
      $('.acq-content-product .content__title_wrapper .promotions .promotions-dynamic-label', context).once('update-promo-label-pdp').each(function () {
        updateAlshayaPromotionsLabel(alshayaAcmPromotions);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
