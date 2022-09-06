import React from 'react';
import ApplePay from '../../../utilities/apple_pay';
import dispatchCustomEvent from '../../../utilities/events';
import { getPayable } from '../../../utilities/checkout_util';

class PaymentMethodApplePay extends React.Component {
  componentDidMount = () => {
    ApplePay.isPossible();
  };

  validateBeforePlaceOrder = () => {
    const { cart } = this.props;
    const payableAmount = getPayable(cart);

    // To add the custom event for the checkout step 4.
    dispatchCustomEvent('orderValidated', {
      cart: cart.cart,
    });
    ApplePay.startPayment(payableAmount);
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

export default PaymentMethodApplePay;
