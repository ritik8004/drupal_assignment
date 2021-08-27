(function ($, Drupal) {

  document.addEventListener('alshayaPostpayInit', () => {
    alshayaPostpayCheckAmount();
  });

  document.addEventListener('refreshCart', (e) => {
    try {
      var cart_total = e.detail.data().totals.base_grand_total;
    }
    catch (e) {
      cart_total = 0;
    }
    // No need to add a condition to check if the amount is changed, Postpay
    // takes care of that.
    $('.postpay-widget').attr('data-amount', (cart_total * drupalSettings.postpay.currency_multiplier).toFixed(0));
    alshayaPostpayCheckAmount();
    window.postpay.ui.refresh();
  });

  function alshayaPostpayCheckAmount() {
    var amount = $('.postpay-widget').attr('data-amount');
    if (typeof amount === 'undefined'
      || !(amount)
      || parseInt(amount, 10) <= 0) {
      return;
    }

    var currency = $('.postpay-widget').attr('data-currency');
    window.postpay.check_amount({
      amount: amount,
      currency: currency,
      callback: function (payment_options) {
        if (payment_options !== null) {
          // Hide Postpay eligibility message if the payment_options is
          // not available.
          $('#postpay-eligibility-message').hide();
        }
        else {
          // Display Postpay eligibility message if the payment_options is
          // not available.
          $('#postpay-eligibility-message').show();
        }
      },
    });
  }
})(jQuery, Drupal);
