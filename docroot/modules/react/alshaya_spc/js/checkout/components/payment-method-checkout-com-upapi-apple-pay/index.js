import React from 'react';
import CheckoutComUpapiApplePay from '../../../utilities/checkout_com_upapi_apple_pay';

class PaymentMethodCheckoutComUpapiApplePay extends React.Component {
  componentDidMount = () => {
    CheckoutComUpapiApplePay.isPossible();
  };

  validateBeforePlaceOrder = () => {
    const { cart } = this.props;
    CheckoutComUpapiApplePay.startPayment(cart.cart.totals.base_grand_total);
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
