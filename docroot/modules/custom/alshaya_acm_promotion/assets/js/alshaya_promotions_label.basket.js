/**
 * @file
 * Alshaya Promotions Label Manager.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaPromotionsBasketManager = {
    attach: function (context) {
      $('#spc-cart').once('alshayaPromotionsBasketManager').on('click', '.promotion-coupon-details .promotion-coupon-code', function () {
        $('#promo-code').val($(this).attr('data-coupon-code'));
        $('#promo-action-button').trigger('click');
      });

      // Create event for select button.
      if ($('.free-gift-listing-modal #select-add-free-gift').length > 0) {
        var selectFreeGiftModal = new CustomEvent('selectFreeGiftModalEvent', {bubbles: true, detail: {}});
        document.dispatchEvent(selectFreeGiftModal);
      }

      // Event for Add Free gift button.
      if ($('.free-gift-detail-modal #add-free-gift').length > 0) {
        var productDetailFreeGiftModal = new CustomEvent('openFreeGiftModalEvent', {bubbles: true, detail: {}});
        document.dispatchEvent(productDetailFreeGiftModal);
      }
    }
  };

})(jQuery, Drupal);
