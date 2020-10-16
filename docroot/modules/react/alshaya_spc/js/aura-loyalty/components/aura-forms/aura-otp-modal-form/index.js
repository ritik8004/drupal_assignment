import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import { validateInfo } from '../../../../utilities/checkout_util';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';

class AuraFormSignUpOTPModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      mobile: '',
      otp: '',
      otpRequested: false,
      otpVerified: false,
    };
  }

  handleChange = (e) => {
    const { name, value } = e.target;

    this.setState((prevState) => ({
      ...prevState,
      [name]: value,
    }));
  }

  processOtpVerification = (e) => {
    const { action } = e.target.dataset;
    const { mobile, otp, otpRequested } = this.state;

    if (mobile) {
      if (!Number.isNaN(Number(mobile))) {
        const validationRequest = validateInfo({ mobile });
        if (validationRequest instanceof Promise) {
          validationRequest.then((result) => {
            if (result.status === 200 && result.data.status) {
              // If not valid mobile number.
              if (result.data.mobile === false) {
                // @TODO: Add strings from controller to manage translation in all modals.
                this.showError('mobile-error', Drupal.t('Please enter valid mobile number.'));
              } else {
                // If valid mobile number, remove error message.
                this.removeError('mobile-error');

                // Check if OTP is not already requested or is resend request,
                // then call send OTP API else call verify OTP API.
                if (action === 'resend' || !otpRequested) {
                  this.sendOtp();
                } else if (otp) {
                  this.removeError('otp-error');
                  this.verifyOtp();
                } else {
                  this.showError('otp-error', Drupal.t('OTP field is required.'));
                }
              }
            }
          });
        }
      } else {
        this.showError('mobile-error', Drupal.t('Mobile number should be numeric.'));
      }
    } else {
      this.showError('mobile-error', Drupal.t('Mobile number field is required.'));
    }
  };

  showError = (elementId, msg) => {
    document.getElementById(elementId).innerHTML = msg;
    document.getElementById(elementId).classList.add('error');
  }

  removeError = (elementId) => {
    document.getElementById(elementId).innerHTML = '';
    document.getElementById(elementId).classList.remove('error');
  }

  sendOtp = () => {
    // API call to send otp.
    const { mobile } = this.state;

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
  };

  verifyOtp = () => {
    // API call to verify otp.
    const { mobile, otp } = this.state;

    const apiUrl = 'post/loyalty-club/verify-otp';
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
        <span className="part">{Drupal.t('We have sent the One Time Pin to your mobile number.')}</span>,
        <span className="part">{Drupal.t('Didn’t receive the One Time Pin?')}</span>,
      ];
    } else {
      description = Drupal.t('We will send a One Time Pin to your both your email address and mobile number.');
    }
    return description;
  };

  render() {
    const {
      closeModal,
    } = this.props;

    const {
      otpRequested,
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
          <SectionTitle>{Drupal.t('Say hello to Aura')}</SectionTitle>
          <a className="close" onClick={() => closeModal()} />
        </div>
        <div className="aura-modal-form">
          <div className="aura-modal-form-items">
            {countryMobileCodeMarkup}
            <TextField
              type="text"
              required
              name="mobile"
              label={Drupal.t('Mobile Number')}
              maxLength={countryMobileCodeMaxLength}
              onChangeCallback={this.handleChange}
            />
            <ConditionalView condition={otpRequested === true}>
              <TextField
                type="text"
                required={false}
                name="otp"
                label={Drupal.t('One Time Pin')}
                onChangeCallback={this.handleChange}
              />
            </ConditionalView>
          </div>
          <div className="aura-modal-form-actions">
            <div className="aura-otp-submit-description">
              {this.getOtpDescription()}
              <ConditionalView condition={otpRequested === true}>
                <span
                  className="resend-otp"
                  data-action="resend"
                  onClick={(e) => this.processOtpVerification(e)}
                >
                  {Drupal.t('Resend Code')}
                </span>
              </ConditionalView>
            </div>
            <div
              className="aura-modal-form-submit"
              data-action="submit"
              onClick={(e) => this.processOtpVerification(e)}
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
