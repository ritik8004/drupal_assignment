import React from 'react';
import OtpInput from 'react-otp-input';
import CodVerifyText from './components/CodVerifyText';
import OtpTimer from './components/OtpTimer';

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
        <div className="cod-otp-form-wrapper">
          <form>
            <OtpInput
              numInputs={otpLength}
              separator={<span>&nbsp;</span>}
            />
            <div className="cod-otp-lower-wrapper">
              <OtpTimer />
              <button type="submit">{Drupal.t('verify', {}, { context: 'cod_mobile_verification' })}</button>
            </div>
          </form>
        </div>
      </div>
    );
  }
}

export default PaymentMethodCodMobileVerification;
