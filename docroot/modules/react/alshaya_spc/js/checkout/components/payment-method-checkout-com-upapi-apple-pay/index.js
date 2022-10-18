import React from 'react';
import CheckoutComUpapiApplePay from '../../../utilities/checkout_com_upapi_apple_pay';
import dispatchCustomEvent from '../../../utilities/events';
import { getPayable } from '../../../utilities/checkout_util';

class PaymentMethodCheckoutComUpapiApplePay extends React.Component {
  componentDidMount = () => {
    CheckoutComUpapiApplePay.isPossible();
  };

  validateBeforePlaceOrder = () => {
    const { cart } = this.props;
    const payableAmount = getPayable(cart);

    // To add the custom event for the checkout step 4.
    dispatchCustomEvent('orderValidated', {
      cart: cart.cart,
    });
    CheckoutComUpapiApplePay.startPayment(payableAmount);
    return false;
  };

  render() {
    return (
      <>
        <div id="is-possible-placeholder" className="error" />
      </>
    );
  }
}

export default PaymentMethodCheckoutComUpapiApplePay;
