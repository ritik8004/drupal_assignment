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
    const { shippingMobileNumber } = this.props;

    if (shippingMobileNumber === null) {
      return (null);
    }

    return (
      <div className="cod-mobile-verify-wrapper">
        <CodVerifyText
          mobileNumber={shippingMobileNumber}
        />
      </div>
    );
  }
}

export default PaymentMethodCodMobileVerification;
