import React from 'react';
import OtpInput from 'react-otp-input';
import OtpTimer from 'otp-timer';
import CodVerifyText from './components/CodVerifyText';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import { getApiEndpoint } from '../../../backend/v2/utility';
import logger from '../../../../../js/utilities/logger';
import Loading from '../../../../../js/utilities/loading';
import { getDefaultErrorMessage } from '../../../../../js/utilities/error';

class PaymentMethodCodMobileVerification extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otp: '',
      // Flag used to show loader on send otp request.
      wait: true,
      // Flag to validate otp.
      // 0 when otp is not validated.
      // 1 when otp is verified and valid.
      // 2 when otp is verified and invalid and show invalid otp message.
      // 3 when error on otp validate request and show default error message.
      otpVerified: 0,
    };
  }

  componentDidMount = () => {
    // Send OTP to mobile number from shipping address.
    this.SendOtpToShippingMobileNumber();
  }

  /**
   * Sennd OTP to mobile number from shipping address.
   */
  SendOtpToShippingMobileNumber = () => {
    // Get Cart Id.
    const cartId = window.commerceBackend.getCartId();

    // Get shipping address mobile number.
    const { shippingMobileNumber } = this.props;

    // Prepare params.
    const params = {
      cartId,
      mobileNumber: shippingMobileNumber.replace('+', ''),
    };

    return callMagentoApi(getApiEndpoint('codMobileVerificationSendOtp', params), 'GET')
      .then((response) => {
        if (hasValue(response.data.error) || !response.data) {
          logger.error('Error while sending otp for COD payment mobile verification. Response: @response', {
            '@response': JSON.stringify(response.data),
          });
        }

        // Clear otp and remove loader.
        this.setState({
          otp: '',
          wait: false,
        });
      })
      .catch((response) => {
        logger.error('Error while sending otp for COD payment mobile verification. Error message: @message, Code: @errorCode', {
          '@message': response.error.message,
          '@errorCode': response.error.error_code,
        });
      });
  };

  /**
   * Validate COD mobile verification before enalbing complete purchase button.
   *
   * // @todo Update for cod otp container.
   */
  validateBeforePlaceOrder = () => false;

  /**
   * Handle user input for otp field.
   */
  handleChange = (otp) => this.setState({
    otp,
    otpVerified: 0,
  });

  handleOtpSubmit = (e) => {
    e.preventDefault();

    // Get otp from state.
    const { otp } = this.state;

    // Get shipping mobile number from props.
    const { shippingMobileNumber } = this.props;

    // Get allowed otp length from props.
    const { otpLength } = this.props;

    if (!hasValue(otp) || otp.length !== parseInt(otpLength, 10)) {
      return false;
    }

    // Prepare params for endpoint.
    const params = {
      cartId: window.commerceBackend.getCartId(),
      mobileNumber: shippingMobileNumber.replace('+', ''),
      otp,
    };

    // Validate otp enter by the user.
    callMagentoApi(getApiEndpoint('codMobileVerificationValidateOtp', params), 'GET')
      .then((response) => {
        if (hasValue(response) && !response.data) {
          this.setState({
            otpVerified: 2,
          });

          return;
        }

        if (hasValue(response.data.error)) {
          logger.error('Error while validating otp for COD payment mobile verification. Response: @response', {
            '@response': JSON.stringify(response.data),
          });
          // Set to 3 to show default error message.
          this.setState({
            otpVerified: 3,
          });

          return;
        }

        if (hasValue(response) && response.data) {
          this.setState({
            otpVerified: 1,
          });
        }
      })
      .catch((response) => {
        logger.error('Error while validating otp for COD payment mobile verification. Error message: @message, Code: @errorCode', {
          '@message': response.error.message,
          '@errorCode': response.error.error_code,
        });
      });

    return true;
  };

  render() {
    const {
      otp, wait, otpVerified,
    } = this.state;
    const { shippingMobileNumber, otpLength } = this.props;

    if (shippingMobileNumber === null) {
      return (null);
    }

    if (wait) {
      return (
        <div className="cod-mobile-otp-waiting-wrapper">
          <Loading />
        </div>
      );
    }

    if (otpVerified === 1) {
      // @todo Implement verified otp component.
    }

    let otpErrorMessage = '';
    if (otpVerified === 2) {
      otpErrorMessage = Drupal.t('Wrong OTP', {}, { context: 'cod_mobile_verification' });
    }
    if (otpVerified === 3) {
      otpErrorMessage = getDefaultErrorMessage();
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
            className={(otpVerified === 2 || otpVerified === 3) ? 'cod-mobile-otp__field error' : 'cod-mobile-otp__field'}
          />
          <div id="otp-error" className="error">
            { otpErrorMessage }
          </div>
          <div className="cod-mobile-otp__controls">
            <span className="cod-mobile-otp__resend">
              {Drupal.t('Didn\'t receive the code?', {}, { context: 'cod_mobile_verification' })}
              <OtpTimer
                seconds={60}
                minutes={0}
                resend={this.SendOtpToShippingMobileNumber}
                text=" "
                ButtonText={Drupal.t('Resend', {}, { context: 'cod_mobile_verification' })}
              />
            </span>
            <button
              type="submit"
              className="cod-mobile-otp__submit"
              onClick={this.handleOtpSubmit}
              disabled={otp.length !== parseInt(otpLength, 10)}
            >
              {Drupal.t('verify', {}, { context: 'cod_mobile_verification' })}
            </button>
          </div>
        </form>
      </div>
    );
  }
}

export default PaymentMethodCodMobileVerification;
