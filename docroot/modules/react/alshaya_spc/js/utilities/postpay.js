const Postpay = {
  isAvailable: (that) => {
    const { cart: { cart } } = that.props;
    const { postpayAvailable } = that.state;
    if (postpayAvailable[cart.cart_total] !== undefined) {
      return postpayAvailable[cart.cart_total];
    }
    Postpay.alshayaPostpayCheckCheckoutAmount(that);
    return null;
  },

  alshayaPostpayCheckCheckoutAmount: (that) => {
    const checkPostpayInitialised = that.props.isPostpayInitialised;
    if (Postpay.isPostpayEnabled() && !checkPostpayInitialised) {
      const postpayTimer = setInterval(() => {
        const { isPostpayInitialised } = that.props;
        if (isPostpayInitialised) {
          clearInterval(postpayTimer);
          Postpay.alshayaPostpayCheckCheckoutAmount(that);
        }
      }, 100);
      return;
    }
    const { postpayAvailable } = that.state;
    const { cart: { cart } } = that.props;
    window.postpay.check_amount({
      amount: cart.cart_total * drupalSettings.postpay.currency_multiplier,
      currency: drupalSettings.postpay_widget_info['data-currency'],
      callback(paymentOptions) {
        postpayAvailable[cart.cart_total] = !(paymentOptions === null);
        that.setState({ postpayAvailable });
      },
    });
  },

  isPostpayEnabled: () => {
    if (typeof drupalSettings.postpay_widget_info !== 'undefined'
      && typeof drupalSettings.postpay !== 'undefined') {
      return true;
    }
    return false;
  },
};

export default Postpay;
