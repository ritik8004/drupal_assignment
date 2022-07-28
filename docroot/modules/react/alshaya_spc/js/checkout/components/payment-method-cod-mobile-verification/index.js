import React from 'react';
import CodVerifyText from './components/CodVerifyText';
import dispatchCustomEvent from '../../../utilities/events';

class PaymentMethodCodMobileVerification extends React.Component {
  componentDidMount() {
    dispatchCustomEvent('refreshCompletePurchaseSection', {});
  }

  componentDidUpdate() {
    dispatchCustomEvent('refreshCompletePurchaseSection', {});
  }

  validateBeforePlaceOrder = () => false

  render() {
    const { shippingMobileNumber, otpLength } = this.props;

    if (shippingMobileNumber === null) {
      return (null);
    }

    return (
      <div className="cod-mobile-verify-wrapper">
        <CodVerifyText
          mobileNumber={shippingMobileNumber}
          otpLength={otpLength}
        />
      </div>
    );
  }
}

export default PaymentMethodCodMobileVerification;
