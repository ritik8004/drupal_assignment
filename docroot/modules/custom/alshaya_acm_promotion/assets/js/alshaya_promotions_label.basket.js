/**
 * @file
 * Alshaya Promotions Label Manager.
 */

(function ($, Drupal) {

  Drupal.behaviors.alshayaPromotionsBasketManager = {
    attach: function (context) {
      $('#spc-cart').once('alshayaPromotionsBasketManager').on('click', '.promotion-coupon-details .promotion-coupon-code', function () {
        $('#promo-code').val($(this).attr('data-coupon-code'));
        $('#promo-action-button').trigger('click');
      });
    }
  };

})(jQuery, Drupal);
