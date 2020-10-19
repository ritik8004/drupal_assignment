import React from 'react';
import Popup from 'reactjs-popup';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import AuraFormNewAuraUserModal from '../aura-new-aura-user-form';
import { validateInfo } from '../../../../utilities/checkout_util';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import getStringMessage from '../../../../utilities/strings';
import { getElementValue, showError, removeError } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';

class AuraFormSignUpOTPModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      mobileNumber: null,
      otpRequested: false,
      isNewUserModalOpen: false,
    };
  }

  openNewUserModal = () => {
    this.setState({
      isNewUserModalOpen: true,
    });
  };

  closeNewUserModal = () => {
    const {
      closeOTPModal,
    } = this.props;

    this.setState({
      isNewUserModalOpen: false,
    });

    // Also close OTP Modal.
    closeOTPModal();
  };

  // Verify OTP and show error.
  validateMobileOtp = (data, action) => {
    let isValid = true;

    if (action === 'send_otp') {
      const validationRequest = validateInfo({ mobile: data.mobile });
      return validationRequest.then((result) => {
        if (result.status === 200 && result.data.status) {
          // If not valid mobile number.
          if (result.data.mobile === false) {
            showError('mobile-error', getStringMessage('form_error_valid_mobile_number'));
            isValid = false;
          } else {
            // If valid mobile number, remove error message.
            removeError('mobile-error');
          }
        }
        return isValid;
      });
    }
    return isValid;
  };

  // Send OTP to the user.
  sendOtp = () => {
    const mobile = getElementValue('mobile');

    if (mobile.length === 0 || mobile.match(/^[0-9]+$/) === null) {
      showError('mobile-error', getStringMessage('form_error_mobile_number'));
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
                    mobileNumber: mobile,
                  });
                }
              }
            });
          }
        }
      });
    }
  };

  // Verify OTP from user.
  verifyOtp = () => {
    const otp = getElementValue('otp');

    if (otp.length === 0) {
      showError('otp-error', getStringMessage('form_error_otp'));
      return;
    }

    removeError('otp-error');
    // API call to verify otp.
    const apiUrl = 'post/loyalty-club/verify-otp';
    const mobile = getElementValue('mobile');
    const apiData = postAPIData(apiUrl, { mobile, otp });

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          // Once we get a success response that OTP is verified, we update state,
          // to show the quick enrollment fields.
          if (result.data.status) {
            // Open modal for the new user.
            this.openNewUserModal();
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
      closeOTPModal,
      handleSignUp,
    } = this.props;

    const {
      otpRequested,
      mobileNumber,
      isNewUserModalOpen,
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

    const submitButtonText = otpRequested === true ? Drupal.t('Verify') : Drupal.t('Send One Time Pin');

    return (
      <div className="aura-otp-form">
        <div className="aura-modal-header">
          <SectionTitle>{getStringMessage('otp_modal_title')}</SectionTitle>
          <a className="close" onClick={() => closeOTPModal()} />
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
            <ConditionalView condition={otpRequested === false}>
              <div
                className="aura-modal-form-submit"
                onClick={() => this.sendOtp()}
              >
                {submitButtonText}
              </div>
            </ConditionalView>
            <ConditionalView condition={otpRequested === true}>
              <>
                <div className="aura-modal-form-submit" onClick={() => this.verifyOtp()}>{submitButtonText}</div>
                <Popup
                  className="aura-modal-form new-aura-user"
                  open={isNewUserModalOpen}
                  closeOnEscape={false}
                  closeOnDocumentClick={false}
                >
                  <AuraFormNewAuraUserModal
                    mobileNumber={mobileNumber}
                    closeNewUserModal={() => this.closeNewUserModal()}
                    closeOTPModal={() => this.closeNewUserModal()}
                    handleSignUp={handleSignUp}
                  />
                </Popup>
              </>
            </ConditionalView>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormSignUpOTPModal;
