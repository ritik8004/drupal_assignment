import React from 'react';
import { getElementValue, removeError, showError } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../../../../../../../js/utilities/events';
import logger from '../../../../../../../js/utilities/logger';
import { callMagentoApi } from '../../../../../../../js/utilities/requestHelper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { getInlineErrorSelector } from '../../../../../aura-loyalty/components/utilities/link_card_sign_up_modal_helper';

class AuraVerifyOTP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otpVerified: false,
    };
  }

  verifyOtp = () => {
    const { mobile, resetModalMessages } = this.props;
    const otp = getElementValue('otp');
    if (otp.length === 0) {
      showError(getInlineErrorSelector('otp').otp, getStringMessage('form_error_otp'));
      return;
    }
    removeError(getInlineErrorSelector('otp').otp);

    showFullScreenLoader();
    callMagentoApi(`/V1/verifyotp/phonenumber/${mobile.replace('+', '')}/otp/${otp}/type/link`, 'GET')
      .then((response) => {
        if (!hasValue(response.data.error)) {
          if (response.data) {
            this.setState({
              otpVerified: response.data,
            });

            dispatchCustomEvent('onCustomerVerification', response.data);
          } else {
            showError(getInlineErrorSelector('otp').otp, getStringMessage('form_error_invalid_otp'));
            logger.notice('Error while trying to verify otp for mobile number @mobile. OTP: @otp. Message: @message', {
              '@mobile': mobile,
              '@otp': otp,
              '@message': 'Invalid OTP entered.',
            });
          }
        }
        if (response.data.error) {
          logger.notice('Error while trying to verify otp for mobile number @mobile. OTP: @otp. Message: @message', {
            '@mobile': mobile,
            '@otp': otp,
            '@message': response.data.error_message,
          });

          let message = getStringMessage(response.data.error_message);
          message = hasValue(message) ? message : response.data.error_message;

          resetModalMessages('error', message);
        }
        removeFullScreenLoader();
      });
  };

  render() {
    const { otpVerified } = this.state;
    return (
      <>
        {!otpVerified
          && (
          <div className="aura-verify-otp">
            <div className="aura-modal-form-submit" onClick={() => this.verifyOtp()}>
              {getStringMessage('verify_otp')}
            </div>
          </div>
          )}
        {otpVerified
          && <div className="aura-redeem-form" />}
      </>
    );
  }
}

export default AuraVerifyOTP;
