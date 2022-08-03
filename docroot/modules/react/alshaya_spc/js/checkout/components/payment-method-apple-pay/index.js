import React from 'react';
import ApplePay from '../../../utilities/apple_pay';
import dispatchCustomEvent from '../../../utilities/events';

class PaymentMethodApplePay extends React.Component {
  componentDidMount = () => {
    ApplePay.isPossible();
  };

  validateBeforePlaceOrder = () => {
    const { cart } = this.props;
    // To add the custom event for the checkout step 4.
    dispatchCustomEvent('orderValidated', {
      cart: cart.cart,
    });
    ApplePay.startPayment(cart.cart.totals.base_grand_total);
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
