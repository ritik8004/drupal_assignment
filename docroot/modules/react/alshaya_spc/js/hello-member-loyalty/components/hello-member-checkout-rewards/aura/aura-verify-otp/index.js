import React from 'react';
import { getElementValue } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import dispatchCustomEvent from '../../../../../../../js/utilities/events';
import logger from '../../../../../../../js/utilities/logger';
import { callMagentoApi } from '../../../../../../../js/utilities/requestHelper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';

class AuraVerifyOTP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otpVerified: false,
    };
  }

  verifyOtp = () => {
    showFullScreenLoader();
    const { mobile } = this.props;
    const otp = getElementValue('otp');
    callMagentoApi(`/V1/verifyotp/phonenumber/${mobile.replace('+', '')}/otp/${otp}/type/link`, 'GET')
      .then((response) => {
        if (response.data) {
          this.setState({
            otpVerified: response.data,
          });

          dispatchCustomEvent('onCustomerVerification', response.data);
        }
        if (response.data.error) {
          logger.notice('Error while trying to verify otp for mobile number @mobile. OTP: @otp. Type: @type. Message: @message', {
            '@mobile': mobile,
            '@otp': otp,
            '@message': response.data.error_message,
          });
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
              {Drupal.t('Verify', {}, { context: 'hello_member' })}
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
