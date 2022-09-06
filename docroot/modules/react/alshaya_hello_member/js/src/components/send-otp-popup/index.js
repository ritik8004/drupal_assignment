import React from 'react';
import Popup from 'reactjs-popup';
import OtpInput from 'react-otp-input';
import { sendOtp, verifyOtp } from '../../../../../js/utilities/otp_helper';
import getStringMessage from '../../../../../js/utilities/strings';
import { helloMemberCustomerPhoneSearch } from '../../hello_member_api_helper';
import { validateInfo } from '../../../../../alshaya_spc/js/utilities/checkout_util';
import { getDefaultErrorMessage } from '../../../../../js/utilities/error';
import { showFullScreenLoader, removeFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';

class SendOtpPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openModal: false,
      hasErrored: false,
      otp: '',
      errorStyle: 'error',
      otpVerified: false,
    };
  }

  // Set otp typed in inputs
  handleChange = (otp) => {
    document.getElementById('hello-member-modal-form-verify').classList.add('in-active');
    if (otp.length === 6) {
      // Only enable verify button if all 6 digit entered in otp field.
      document.getElementById('hello-member-modal-form-verify').classList.remove('in-active');
    }
    this.setState({ otp });
  };

  // Open Modal.
  onClickSendOtp = (e) => {
    this.validatePhoneNumber();
    e.preventDefault();
  };

  validatePhoneNumber = () => {
    // Validate the phone number
    const validationData = {
      mobile: document.getElementById('edit-field-mobile-number-0-mobile').value,
    };
    validateInfo(validationData).then((response) => {
      if (!response || response.data.status === undefined || !response.data.status) {
        this.setErrorMsgforPhone(getDefaultErrorMessage());
      }
      // If invalid mobile number.
      if (response.data.mobile === false) {
        this.setErrorMsgforPhone(Drupal.t('Please enter valid mobile number.', {}, { context: 'hello_member' }));
      } else {
        this.unsetErrorMsgforPhone();
        // if valid phone number call send otp api.
        this.callSendOtpApi();
      }
    });
  };

  // Toggle to set state for popup.
  toggleSendOtpPopup = (openModal) => {
    this.setState({
      openModal,
    });
  };

  // Set error message for phone number field
  setErrorMsgforPhone = (errMsg) => {
    document.getElementById('mobile-number-error').innerHTML = errMsg;
    document.getElementById('mobile-number-error').classList.add('error');
  };

  // unset error message for phone number field
  unsetErrorMsgforPhone = () => {
    if (document.querySelector('#mobile-number-error').classList.contains('error')) {
      document.getElementById('mobile-number-error').innerHTML = '';
      document.getElementById('mobile-number-error').classList.remove('error');
    }
  };


  // send OTP.
  callSendOtpApi = () => {
    const phoneNumber = `${drupalSettings.alshaya_mobile_prefix.slice(1)}${document.getElementById('edit-field-mobile-number-0-mobile').value}`;
    // Check the entered phone number is already in use by another customer.
    showFullScreenLoader();
    const phoneSearchResponse = helloMemberCustomerPhoneSearch(phoneNumber);
    if (phoneSearchResponse instanceof Promise) {
      phoneSearchResponse.then((phoneResult) => {
        if (phoneResult.status !== 200) {
          // If Phone SearchAPI is returning Error.
          this.setErrorMsgforPhone(phoneResult.data.error_message);
          return;
        }
        if (phoneResult.data.apc_identifier_number !== null
          && phoneResult.data.error === null
          && phoneResult.status === 200) {
          this.setErrorMsgforPhone(Drupal.t('This Phone number is already in use.', {}, { context: 'hello_member' }));
        } else {
          // Call send otp Api only if the Phone number is not in use by any other customer.
          const responseData = sendOtp(
            phoneNumber,
            'reg',
          );
          if (responseData instanceof Promise) {
            responseData.then((result) => {
              if (result.error !== undefined || !result.status) {
                this.setErrorMsgforPhone(result.error_message);
                return;
              }
              this.setState({
                otp: '',
                hasErrored: false,
              });
              this.toggleSendOtpPopup(true);
              document.getElementById('hello-member-modal-form-verify').classList.add('in-active');
              document.getElementById('input-otp-error').innerHTML = '';
            });
          }
        }
      });
    }
    removeFullScreenLoader();
  };

  // Verify OTP
  onClickVerify = () => {
    const { otp } = this.state;
    showFullScreenLoader();
    const responseData = verifyOtp(
      `${document.getElementById('edit-field-mobile-number-0-mobile').value}`,
      otp,
      'reg',
      `${drupalSettings.alshaya_mobile_prefix.slice(1)}`,
    );
    if (responseData instanceof Promise) {
      responseData.then((result) => {
        // show error message if  otp verification api fails / returns false.
        if (!result.data.status || result.data.error !== undefined) {
          // Set error for OTP field if entered wrong OTP.
          document.getElementById('input-otp-error').innerHTML = Drupal.t('Please enter valid OTP', {}, { context: 'hello_member' });
          this.setState({
            hasErrored: true,
          });
          removeFullScreenLoader();
          return;
        }
        this.setState({ otpVerified: true });
        this.toggleSendOtpPopup(false);
        // If successfully verified make the otp verified update otp_verified.
        document.querySelector('input[name="otp_verified"]').value = 1;
        document.getElementById('edit-submit').classList.remove('in-active');
      });
    }
    removeFullScreenLoader();
  };

  render() {
    const {
      openModal,
      hasErrored,
      otp,
      errorStyle,
      otpVerified,
    } = this.state;
    let phoneNumberMsg = Drupal.t('OTP will be send to your mobile number to verify', {}, { context: 'hello_member' });
    if (otpVerified) {
      phoneNumberMsg = (
        <span className="verified-msg">
          { Drupal.t('Verified', {}, { context: 'hello_member' }) }
        </span>
      );
    }
    return (
      <>
        <div className="btn-wrapper in-active">
          <button onClick={(e) => this.onClickSendOtp(e)} type="button">{getStringMessage('send_otp_label')}</button>
        </div>
        <div id="mobile-number-error" />
        <div className="mb-verifier">
          {phoneNumberMsg}
        </div>
        <div className="popup-container">
          <Popup
            open={openModal}
            closeOnDocumentClick={false}
            closeOnEscape={false}
          >
            <div className="hello-member-otp-popup-form">
              <a className="close-modal" onClick={() => this.toggleSendOtpPopup(false)} />
              <div className="opt-title">
                <p>{ getStringMessage('sent_otp_message') }</p>
                <p>{ document.getElementById('edit-field-mobile-number-0-mobile').value }</p>
              </div>
              <OtpInput
                isInputNum
                errorStyle={errorStyle}
                value={otp}
                onChange={this.handleChange}
                numInputs={6}
                hasErrored={hasErrored}
                separator={<span />}
                shouldAutoFocus
              />
              <label id="input-otp-error" className="error" />
            </div>
            <div className="hello-member-modal-form-actions">
              <div id="hello-member-modal-form-verify" className="hello-member-modal-form-submit in-active" onClick={() => this.onClickVerify()}>{ getStringMessage('verify_label') }</div>
              <div className="hello-member-otp-submit-description">
                <span>{ getStringMessage('resend_otp_desc') }</span>
                <a className="hello-member-modal-form-resend-otp" onClick={() => this.callSendOtpApi()}>{ getStringMessage('resend_code_label') }</a>
              </div>
            </div>
          </Popup>
        </div>
      </>
    );
  }
}

export default SendOtpPopup;
