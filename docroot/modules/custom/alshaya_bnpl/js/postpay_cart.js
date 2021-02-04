(function ($, Drupal) {
  document.addEventListener('refreshCart', (e) => {
    var cart_total = e.detail.data().totals.base_grand_total;
    setPostpayWidgetAmount(cart_total);
  });

  function setPostpayWidgetAmount(cart_total) {
    // No need to add a condition to check if the amount is changed, Postpay
    // takes care of that.
    $('.postpay-widget').attr('data-amount', cart_total * drupalSettings.postpay.currency_multiplier);
    postpay.ui.refresh();
  }
})(jQuery, Drupal);
