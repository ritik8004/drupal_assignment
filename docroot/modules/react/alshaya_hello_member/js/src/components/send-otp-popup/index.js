import React from 'react';
import Popup from 'reactjs-popup';
import OtpInput from 'react-otp-input';
import { sendOtp, verifyOtp } from '../../../../../js/utilities/otp_helper';
import getStringMessage from '../../../../../js/utilities/strings';

class SendOtpPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openModal: false,
      otp: '',
    };
  }

  handleChange = (otp) => this.setState({ otp });

  // Open Modal.
  onClickSendOtp = (e) => {
    this.callSendOtpApi();
    e.preventDefault();
  };

  // Toggle to set state for popup.
  toggleSendOtpPopup = (openModal) => {
    this.setState({
      openModal,
    });
  };

  /**
   * Handle Error message for otp field.
   */
  handleErrorMessage = (isError) => {
    if (document.getElementById('input-otp').value.length !== 6 || isError) {
      document.getElementById('input-otp-error').innerHTML = Drupal.t('Please enter valid OTP', {}, { context: 'hello_member' });
      document.getElementById('input-otp-error').classList.add('error');
      return;
    }
    // Reset error message to empty.
    document.getElementById('input-otp-error').innerHTML = '';
  };

  // Resend OTP.
  callSendOtpApi = () => {
    const responseData = sendOtp(
      `${drupalSettings.alshaya_mobile_prefix.slice(1)}${document.getElementById('edit-field-mobile-number-0-mobile').value}`,
      'reg',
    );
    if (responseData instanceof Promise) {
      responseData.then((result) => {
        if (result.error !== undefined || !result.status) {
          document.getElementById('mobile-number-error').innerHTML = Drupal.t('Something went wrong please try again later', {}, { context: 'hello_member' });
          document.getElementById('mobile-number-error').classList.add('error');
          return;
        }
        this.toggleSendOtpPopup(true);
      });
    }
  };

  // Verify OTP
  onClickVerify = () => {
    this.handleErrorMessage(false);
    const responseData = verifyOtp(
      `${document.getElementById('edit-field-mobile-number-0-mobile').value}`,
      `${document.getElementById('input-otp').value}`,
      'reg',
      `${drupalSettings.alshaya_mobile_prefix.slice(1)}`,
    );
    if (responseData instanceof Promise) {
      responseData.then((result) => {
        // show error message if  otp verification api fails / returns false.
        if (!result.data.status || result.data.error !== undefined) {
          this.handleErrorMessage(true);
          return;
        }
        this.toggleSendOtpPopup(false);
        // If successfully verified make the otp verified checkbox seleted.
        document.getElementById('edit-field-verified-otp-value').click();
      });
    }
  };

  render() {
    const { openModal } = this.state;
    const { otp } = this.state;
    return (
      <>
        <div className="btn-wrapper">
          <button onClick={(e) => this.onClickSendOtp(e)} type="button">{getStringMessage('send_otp_label')}</button>
        </div>
        <div className="popup-container">
          <Popup
            open={openModal}
            closeOnDocumentClick={false}
            closeOnEscape={false}
          >
            <div className="hello-member-otp-popup-form">
              <a className="close-modal" onClick={() => this.toggleSendOtpPopup(false)}>X</a>
              <div className="opt-title">
                <p>{ getStringMessage('sent_otp_message') }</p>
                <p>{ document.getElementById('edit-field-mobile-number-0-mobile').value }</p>
              </div>
              <OtpInput
                value={otp}
                onChange={this.handleChange}
                numInputs={6}
                separator={<span />}
              />
              <label id="input-otp-error" className="error" />
            </div>
            <div className="hello-member-modal-form-actions">
              <div className="hello-member-modal-form-submit" onClick={() => this.onClickVerify()}>{ getStringMessage('verify_label') }</div>
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
