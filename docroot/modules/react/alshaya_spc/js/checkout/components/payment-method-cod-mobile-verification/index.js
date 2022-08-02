import React from 'react';
import OtpInput from 'react-otp-input';
import CodVerifyText from './components/CodVerifyText';
import OtpTimer from './components/OtpTimer';

class PaymentMethodCodMobileVerification extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otp: '',
    };
  }

  /**
   * Validate COD mobile verification before enalbing complete purchase button.
   *
   * // @todo Update for cod otp container.
   */
  validateBeforePlaceOrder = () => false;

  /**
   * Handle user input for otp field.
   */
  handleChange = (otp) => this.setState({ otp });

  render() {
    const { otp } = this.state;

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
              value={otp}
              onChange={this.handleChange}
              numInputs={otpLength}
              separator={<span>&nbsp;</span>}
              isInputNum
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
