const Postpay = {
  isAvailable: (postpayAvailable, cart, setState) => {
    if (postpayAvailable[cart.cart_total] !== undefined) {
      return postpayAvailable[cart.cart_total];
    }
    Postpay.alshayaPostpayCheckCheckoutAmount(postpayAvailable, cart, setState);
    return null;
  },

  alshayaPostpayCheckCheckoutAmount: (postpayAvailable, cart, setState) => {
    window.postpay.check_amount({
      amount: cart.cart_total * drupalSettings.postpay.currency_multiplier,
      currency: drupalSettings.postpay_widget_info['data-currency'],
      callback(paymentOptions) {
        const temp = postpayAvailable;
        temp[cart.cart_total] = !(paymentOptions === null);
        setState({ postpayAvailable: temp });
      },
    });
  },
};

export default Postpay;
