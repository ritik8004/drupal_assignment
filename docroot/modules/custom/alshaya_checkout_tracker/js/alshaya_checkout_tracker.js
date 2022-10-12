(function ($, Drupal) {
  Drupal.behaviors.checkouttracker = {
    attach: function (context, settings) {
      const cartData = Drupal.alshayaSpc.getCartData();
      if (!(cartData && Drupal.hasValue(cartData.items))) {
        $('#block-checkouttrackerblock').addClass('hide-checkout-tracker');
      }
    }
  }
})(jQuery, Drupal);
