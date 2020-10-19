import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import { validateInfo } from '../../../../utilities/checkout_util';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import getStringMessage from '../../../../utilities/strings';

class AuraFormSignUpOTPModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otpRequested: false,
      otpVerified: false,
    };
  }

  getElementValue = (elementId) => document.getElementById(elementId).value

  showError = (elementId, msg) => {
    document.getElementById(elementId).innerHTML = msg;
    document.getElementById(elementId).classList.add('error');
  }

  removeError = (elementId) => {
    document.getElementById(elementId).innerHTML = '';
    document.getElementById(elementId).classList.remove('error');
  }

  validateMobileOtp = (data, action) => {
    let isValid = true;

    if (action === 'send_otp') {
      const validationRequest = validateInfo({ mobile: data.mobile });
      return validationRequest.then((result) => {
        if (result.status === 200 && result.data.status) {
          // If not valid mobile number.
          if (result.data.mobile === false) {
            this.showError('mobile-error', getStringMessage('form_error_valid_mobile_number'));
            isValid = false;
          } else {
            // If valid mobile number, remove error message.
            this.removeError('mobile-error');
          }
        }
        return isValid;
      });
    }
    return isValid;
  }

  sendOtp = () => {
    const mobile = this.getElementValue('mobile');

    if (mobile.length === 0 || mobile.match(/^[0-9]+$/) === null) {
      this.showError('mobile-error', getStringMessage('form_error_mobile_number'));
      return;
    }

    // Call API to check if mobile number is valid.
    const validationRequest = this.validateMobileOtp({ mobile }, 'send_otp');
    if (validationRequest instanceof Promise) {
      validationRequest.then((valid) => {
        if (valid === true) {
          // API call to send otp.
          const apiUrl = 'post/loyalty-club/send-otp';
          const apiData = postAPIData(apiUrl, { mobile });

          if (apiData instanceof Promise) {
            apiData.then((result) => {
              if (result.data !== undefined && result.data.error === undefined) {
                // Once we get a success response that OTP is sent, we update state,
                // to show the otp fields.
                if (result.data.status) {
                  this.setState({
                    otpRequested: true,
                  });
                }
              }
            });
          }
        }
      });
    }
  };

  verifyOtp = () => {
    const otp = this.getElementValue('otp');

    if (otp.length === 0) {
      this.showError('otp-error', getStringMessage('form_error_otp'));
      return;
    }

    this.removeError('otp-error');
    // API call to verify otp.
    const apiUrl = 'post/loyalty-club/verify-otp';
    const mobile = this.getElementValue('mobile');
    const apiData = postAPIData(apiUrl, { mobile, otp });

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          // Once we get a success response that OTP is verified, we update state,
          // to show the quick enrollment fields.
          if (result.data.status) {
            this.setState({
              otpVerified: true,
            });
          }
        }
      });
    }
  };

  getOtpDescription = () => {
    const {
      otpRequested,
    } = this.state;

    let description = '';
    if (otpRequested === true) {
      description = [
        <span key="part1" className="part">{getStringMessage('otp_send_message')}</span>,
        <span key="part2" className="part">{getStringMessage('didnt_receive_otp_message')}</span>,
      ];
    } else {
      description = getStringMessage('send_otp_helptext');
    }
    return description;
  };

  render() {
    const {
      closeModal,
    } = this.props;

    const {
      otpRequested,
      otpVerified,
    } = this.state;

    const {
      country_mobile_code: countryMobileCode,
      mobile_maxlength: countryMobileCodeMaxLength,
    } = getAuraConfig();

    const countryMobileCodeMarkup = countryMobileCode
      ? (
        <span className="country-code">
          +
          {countryMobileCode}
        </span>
      )
      : '';

    const submitButtonText = otpRequested === true ? getStringMessage('verify') : getStringMessage('otp_button_label');

    if (otpVerified) {
      // @TODO: If otp is successfully verified, create
      // component for quick enrollment modal and render.
    }

    return (
      <div className="aura-otp-form">
        <div className="aura-modal-header">
          <SectionTitle>{getStringMessage('otp_modal_title')}</SectionTitle>
          <a className="close" onClick={() => closeModal()} />
        </div>
        <div className="aura-modal-form">
          <div className="aura-modal-form-items">
            {countryMobileCodeMarkup}
            <TextField
              type="text"
              required
              name="mobile"
              label={getStringMessage('mobile_label')}
              maxLength={countryMobileCodeMaxLength}
            />
            <ConditionalView condition={otpRequested === true}>
              <TextField
                type="text"
                required={false}
                name="otp"
                label={getStringMessage('otp_label')}
              />
            </ConditionalView>
          </div>
          <div className="aura-modal-form-actions">
            <div className="aura-otp-submit-description">
              {this.getOtpDescription()}
              <ConditionalView condition={otpRequested === true}>
                <span
                  className="resend-otp"
                  onClick={this.sendOtp}
                >
                  {getStringMessage('resend_code')}
                </span>
              </ConditionalView>
            </div>
            <div
              className="aura-modal-form-submit"
              onClick={otpRequested === true ? this.verifyOtp : this.sendOtp}
            >
              {submitButtonText}
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormSignUpOTPModal;
