import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import { validateInfo } from '../../../../utilities/checkout_util';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import getStringMessage from '../../../../utilities/strings';
import AuraMobileNumberField from '../aura-mobile-number-field';
import { getElementValue, showError, removeError } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import AuraFormModalMessage from '../aura-form-modal-message';

class AuraFormSignUpOTPModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otpRequested: false,
      messageType: null,
      messageContent: null,
    };
  }

  getOtpModalData = () => {
    const otpModalData = {
      mobile: getElementValue('otp-mobile-number'),
      otp: getElementValue('otp'),
    };

    return otpModalData;
  };

  resetModalMessages = () => {
    // Reset/Remove if any message is displayed.
    this.setState({
      messageType: null,
      messageContent: null,
    });
  };

  resetModalField = (elementId) => {
    const element = document.getElementById(elementId);
    if (element) {
      element.value = '';
      removeError(`${elementId}-error`);
    }
  };

  // Verify OTP and show error.
  validateMobileOtp = (data, action) => {
    let isValid = true;

    if (action === 'send_otp') {
      const { chosenCountryCode } = this.props;
      const validationRequest = validateInfo({ mobile: data.userMobile, chosenCountryCode });
      showFullScreenLoader();
      return validationRequest.then((result) => {
        if (result.status === 200 && result.data.status) {
          // If not valid mobile number.
          if (result.data.mobile === false) {
            showError('otp-aura-mobile-field-error', getStringMessage('form_error_valid_mobile_number'));
            isValid = false;
          } else {
            // If valid mobile number, remove error message.
            removeError('otp-aura-mobile-field-error');
          }
        }
        removeFullScreenLoader();
        return isValid;
      });
    }
    return isValid;
  };

  // Send OTP to the user.
  sendOtp = () => {
    this.resetModalMessages();
    this.resetModalField('otp');
    const { mobile: userMobile } = this.getOtpModalData();
    const {
      setChosenUserMobile,
    } = this.props;

    setChosenUserMobile(userMobile);

    if (userMobile.length === 0 || userMobile.match(/^[0-9]+$/) === null) {
      showError('otp-aura-mobile-field-error', getStringMessage('form_error_mobile_number'));
      return;
    }

    // Call API to check if mobile number is valid.
    const validationRequest = this.validateMobileOtp({ userMobile }, 'send_otp');
    if (validationRequest instanceof Promise) {
      validationRequest.then((valid) => {
        if (valid === true) {
          // API call to send otp.
          const { chosenCountryCode } = this.props;
          const apiUrl = 'post/loyalty-club/send-otp';
          const apiData = postAPIData(apiUrl, { mobile: userMobile, chosenCountryCode });
          showFullScreenLoader();

          if (apiData instanceof Promise) {
            apiData.then((result) => {
              if (result.data !== undefined) {
                if (result.data.error === undefined) {
                  // Once we get a success response that OTP is sent, we update state,
                  // to show the otp fields.
                  if (result.data.status) {
                    this.setState({
                      otpRequested: true,
                    });
                  }
                } else if (result.data.error_code === 'already_registered') {
                  showError('otp-aura-mobile-field-error', getStringMessage('form_error_mobile_already_registered'));
                }
              } else {
                this.setState({
                  messageType: 'error',
                  messageContent: getStringMessage('form_error_send_otp_failed_message'),
                });
              }
              removeFullScreenLoader();
            });
          }
        }
      });
    }
  };

  // Verify OTP from user.
  verifyOtp = () => {
    this.resetModalMessages();
    const { otp, mobile } = this.getOtpModalData();

    const {
      closeOTPModal,
      openNewUserModal,
    } = this.props;

    if (otp.length === 0) {
      showError('otp-error', getStringMessage('form_error_otp'));
      return;
    }

    removeError('otp-error');
    // API call to verify otp.
    const apiUrl = 'post/loyalty-club/verify-otp';
    const apiData = postAPIData(apiUrl, { mobile, otp });
    showFullScreenLoader();

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          // Once we get a success response that OTP is verified, we update state,
          // to show the quick enrollment fields.
          if (result.data.status) {
            // Close the OTP Modal.
            closeOTPModal();
            // Open modal for the new user.
            openNewUserModal();
          }
          showError('otp-error', getStringMessage('form_error_invalid_otp'));
        }
        removeFullScreenLoader();
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
      setChosenCountryCode,
    } = this.props;

    const {
      otpRequested,
      messageType,
      messageContent,
    } = this.state;

    const {
      country_mobile_code: countryMobileCode,
      mobile_maxlength: countryMobileCodeMaxLength,
    } = getAuraConfig();

    const submitButtonText = otpRequested === true ? Drupal.t('Verify') : Drupal.t('Send One Time Pin');

    return (
      <div className="aura-otp-form">
        <div className="aura-modal-header">
          <SectionTitle>{getStringMessage('otp_modal_title')}</SectionTitle>
          <button type="button" className="close" onClick={() => closeOTPModal()} />
        </div>
        <div className="aura-modal-form">
          <div className="aura-modal-form-items">
            <div className="aura-form-messages-container">
              <AuraFormModalMessage
                messageType={messageType}
                messageContent={messageContent}
              />
            </div>
            <AuraMobileNumberField
              isDisabled={false}
              name="otp"
              countryMobileCode={countryMobileCode}
              maxLength={countryMobileCodeMaxLength}
              setCountryCode={setChosenCountryCode}
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
              <div className="aura-modal-form-submit" onClick={() => this.verifyOtp()}>{submitButtonText}</div>
            </ConditionalView>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormSignUpOTPModal;
