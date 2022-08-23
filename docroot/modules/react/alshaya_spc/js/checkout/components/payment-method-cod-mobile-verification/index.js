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
import CodVerifiedText from './components/CodVerifiedText';

class PaymentMethodCodMobileVerification extends React.Component {
  constructor(props) {
    super(props);
    const { otpVerified } = this.props;
    this.state = {
      otp: '',
      // Flag used to show loader on send otp request.
      wait: (otpVerified === 0),
      // Flag to validate otp.
      // 0 when otp is not validated.
      // 1 when otp is verified and valid.
      // 2 when otp verified Recently, no need to verify again.
      // 3 when otp is invalid and show invalid otp message.
      // 4 when error on otp validate request and show default error message.
      otpVerified,
    };
  }

  componentDidMount = () => {
    // Send OTP to mobile number from shipping address.
    const { otpVerified } = this.props;
    if (otpVerified === 0) {
      // If otpVerified is 0 then mobile number is not validated, hence send
      // OTP to shipping address mobile number.
      this.sendOtpToShippingMobileNumber();

      // Disable complete purchase button.
      this.disableCompletePurchaseButton();
    }
  }

  componentDidUpdate(prevProps) {
    const { shippingMobileNumber } = this.props;
    if (shippingMobileNumber !== prevProps.shippingMobileNumber) {
      // Update otp Verified flag and reset otp input.
      this.updateOtpVerifiedFlag();

      // Disable complete purchase button.
      // Disable complete purchase button.
      this.disableCompletePurchaseButton();
    }
  }

  /**
   * Disables complete purchase button.
   */
  disableCompletePurchaseButton = () => {
    const completePurchaseCTA = document.querySelector('.complete-purchase-cta');
    if (completePurchaseCTA !== null) {
      completePurchaseCTA.classList.add('in-active');
    }
  };

  /**
   * Update otpVerified when user changes shipping mobile number.
   */
  updateOtpVerifiedFlag = () => {
    this.setState({
      otpVerified: 0,
    },
    // Send OTP to updated mobile number from shipping address.
    () => this.sendOtpToShippingMobileNumber());
  };

  /**
   * Sennd OTP to mobile number from shipping address.
   */
  sendOtpToShippingMobileNumber = () => {
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
  validateBeforePlaceOrder = () => {
    const { otpVerified } = this.state;
    if (otpVerified === 1 || otpVerified === 2) {
      // otpVerified is 1 when user validates mobile in payment method
      // and otpVerified is 2 when user has validate otp recently and received
      // in cart data.
      return true;
    }

    // User has not yet validated mobile number using otp, hence retur false.
    return false;
  };

  /**
   * Handle user input for otp field.
   */
  handleChange = (otp) => this.setState({
    otp,
    otpVerified: 0,
  });

  /**
   * Handle Otp form submit.
   */
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
          // Set to 3 for invalid otp and show invalid otp message.
          this.setState({
            otpVerified: 3,
          });

          return;
        }

        if (hasValue(response.data.error)) {
          logger.error('Error while validating otp for COD payment mobile verification. Response: @response', {
            '@response': JSON.stringify(response.data),
          });
          // Set to 4 to show default error message.
          this.setState({
            otpVerified: 4,
          });

          return;
        }

        if (hasValue(response) && response.data) {
          // Enable complete purchase button.
          const completePurchaseCTA = document.querySelector('.complete-purchase-cta');
          if (completePurchaseCTA !== null) {
            completePurchaseCTA.classList.remove('in-active');
          }

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
        // Set to 4 to show default error message.
        this.setState({
          otpVerified: 4,
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

    if (otpVerified === 1 || otpVerified === 2) {
      return (
        <CodVerifiedText mobileNumber={shippingMobileNumber} />
      );
    }

    let otpErrorMessage = '';
    if (otpVerified === 3) {
      otpErrorMessage = Drupal.t('Wrong OTP', {}, { context: 'cod_mobile_verification' });
    }
    if (otpVerified === 4) {
      otpErrorMessage = getDefaultErrorMessage();
    }

    return (
      <div className="cod-mobile-otp">
        {wait && <Loading />}
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
            className={(otpVerified === 3 || otpVerified === 4) ? 'cod-mobile-otp__field error' : 'cod-mobile-otp__field'}
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
                resend={this.sendOtpToShippingMobileNumber}
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
