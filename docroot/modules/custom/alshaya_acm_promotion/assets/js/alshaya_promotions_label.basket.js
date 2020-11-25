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
      var selectButton = $('.free-gift-listing-modal #select-add-free-gift');
      if (selectButton) {
        var selectFreeGiftModal = new CustomEvent('selectFreeGiftModalEvent', {bubbles: true, detail: {}});
        document.dispatchEvent(selectFreeGiftModal);
      }
    }
  };

})(jQuery, Drupal);
