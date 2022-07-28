import React from 'react';
import CodVerifyText from './components/CodVerifyText';

class PaymentMethodCodMobileVerification extends React.Component {
  /**
   * Validate COD mobile verification before enalbing complete purchase button.
   *
   * // @todo Update for cod otp container.
   */
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
