const Postpay = {
  isAvailable: (that) => {
    const { cart: { cart } } = that.props;
    const { postpayAvailable } = that.state;
    // Send the cart amount eligibility if already available.
    if (postpayAvailable[cart.cart_total] !== undefined) {
      return postpayAvailable[cart.cart_total];
    }
    // Check & set the cart amount eligibility for postpay if not available.
    Postpay.alshayaPostpayCheckCheckoutAmount(that);
    return null;
  },

  alshayaPostpayCheckCheckoutAmount: (that) => {
    const checkPostpayInitialised = that.props.isPostpayInitialised;
    // If postpay is enabled but not initialised.
    if (Postpay.isPostpayEnabled() && !checkPostpayInitialised) {
      // Wait till the postpay is initialised.
      const postpayTimer = setInterval(() => {
        const { isPostpayInitialised } = that.props;
        if (isPostpayInitialised) {
          clearInterval(postpayTimer);
          // Check & set the cart amount eligibility for postpay.
          Postpay.alshayaPostpayCheckCheckoutAmount(that);
        }
      }, 100);
      return;
    }
    // If postpay is enabled and initialised check the cart amount eligibility
    // for postpay.
    const { postpayAvailable } = that.state;
    const { cart: { cart } } = that.props;
    window.postpay.check_amount({
      amount: (cart.cart_total * drupalSettings.postpay.currency_multiplier).toFixed(0),
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
