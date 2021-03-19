import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import getStringMessage from '../../../../utilities/strings';
import AuraMobileNumberField from '../aura-mobile-number-field';
import { showError, removeError } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import AuraFormModalMessage from '../aura-form-modal-message';
import {
  resetInputElement,
  resetInlineError,
  getElementValueByType,
  getInlineErrorSelector,
} from '../../utilities/link_card_sign_up_modal_helper';
import { validateMobile, validateElementValueByType } from '../../utilities/validation_helper';

class AuraFormSignUpOTPModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otpRequested: false,
      messageType: null,
      messageContent: null,
    };
  }

  resetModalMessages = () => {
    // Reset/Remove if any message is displayed.
    this.setState({
      messageType: null,
      messageContent: null,
    });
  };

  // Send OTP to the user.
  sendOtp = () => {
    this.resetModalMessages();
    resetInputElement('otp');
    resetInlineError('otp');
    const userMobile = getElementValueByType('signUpOtpMobile');
    const {
      setChosenUserMobile,
      chosenCountryCode,
    } = this.props;

    setChosenUserMobile(userMobile);
    const isValid = validateElementValueByType('signUpOtpMobile');

    if (isValid === false) {
      return;
    }

    // Call API to check if mobile number is valid.
    const data = {
      mobile: userMobile,
      chosenCountryCode,
    };
    const validationRequest = validateMobile('signUpOtpMobile', data);
    if (validationRequest instanceof Promise) {
      validationRequest.then((valid) => {
        if (valid === true) {
          // API call to send otp.
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
                } else if (result.data.error_code === 'mobile_already_registered') {
                  showError(getInlineErrorSelector('signUpOtpMobile').signUpOtpMobile, getStringMessage(result.data.error_message));
                } else {
                  this.setState({
                    messageType: 'error',
                    messageContent: getStringMessage(result.data.error_message),
                  });
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
    const mobile = getElementValueByType('signUpOtpMobile');
    const otp = getElementValueByType('otp');
    const {
      closeOTPModal,
      openNewUserModal,
    } = this.props;

    if (otp.length === 0) {
      showError(getInlineErrorSelector('otp').otp, getStringMessage('form_error_otp'));
      return;
    }

    removeError(getInlineErrorSelector('otp').otp);
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
          showError(getInlineErrorSelector('otp').otp, getStringMessage('form_error_invalid_otp'));
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

    const submitButtonText = otpRequested === true ? Drupal.t('Verify') : Drupal.t('Send one time PIN');

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
