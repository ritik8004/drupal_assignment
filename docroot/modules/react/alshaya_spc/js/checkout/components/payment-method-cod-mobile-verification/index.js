import React from 'react';
import OtpInput from 'react-otp-input';
import OtpTimer from 'otp-timer';
import CodVerifyText from './components/CodVerifyText';

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

  /**
   * Handle otp resend action.
   *
   * @todo Implement resend otp API endpoint.
   */
  handleResendOtp = () => {
    // eslint-disable-next-line no-console
    console.log('Resend otp');
  }

  render() {
    const { otp } = this.state;
    const { shippingMobileNumber, otpLength } = this.props;

    if (shippingMobileNumber === null) {
      return (null);
    }

    return (
      <div className="cod-mobile-otp">
        <CodVerifyText
          mobileNumber={shippingMobileNumber}
          otpLength={otpLength}
        />
        <form className="cod-mobile-otp__form">
          <OtpInput
            value={otp}
            onChange={this.handleChange}
            numInputs={otpLength}
            isInputNum
            className="cod-mobile-otp__field"
          />
          <div className="cod-mobile-otp__controls">
            <span className="cod-mobile-otp__resend">
              {Drupal.t('Didn\'t receive the code?', {}, { context: 'cod_mobile_verification' })}
              <OtpTimer
                seconds={60}
                minutes={0}
                resend={this.handleResendOtp}
                text=" "
                ButtonText={Drupal.t('Resend', {}, { context: 'cod_mobile_verification' })}
              />
            </span>
            <button type="submit" className="cod-mobile-otp__submit">{Drupal.t('verify', {}, { context: 'cod_mobile_verification' })}</button>
          </div>
        </form>
      </div>
    );
  }
}

export default PaymentMethodCodMobileVerification;
