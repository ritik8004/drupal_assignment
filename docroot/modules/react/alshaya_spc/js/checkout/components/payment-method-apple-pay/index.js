import React from 'react';
import ApplePay from '../../../utilities/apple_pay';

class PaymentMethodApplePay extends React.Component {
  componentDidMount = () => {
    ApplePay.isPossible();
  };

  validateBeforePlaceOrder = () => {
    const { cart } = this.props;
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
