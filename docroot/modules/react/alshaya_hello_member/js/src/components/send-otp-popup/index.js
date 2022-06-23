import React from 'react';
import Popup from 'reactjs-popup';
import { sendOtp, verifyOtp } from '../../../../../js/utilities/otp_helper';
import getStringMessage from '../../../../../js/utilities/strings';

class SendOtpPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openModal: false,
    };
  }

  // Open Modal.
  openModal = (e) => {
    const responseData = sendOtp(
      `${drupalSettings.alshaya_mobile_prefix.slice(1)}${document.getElementById('edit-field-mobile-number-0-mobile').value}`,
      'reg',
    );
    if (responseData instanceof Promise) {
      responseData.then((result) => {
        if (result.status !== undefined) {
          this.setState({
            openModal: true,
          });
        }
      });
    }
    e.preventDefault();
  };

  // Close Modal.
  closeModal = () => {
    this.setState({
      openModal: false,
    });
  };

  // Resend OTP.
  resendOtp = () => {
    const responseData = sendOtp(
      `${drupalSettings.alshaya_mobile_prefix.slice(1)}${document.getElementById('edit-field-mobile-number-0-mobile').value}`,
      'reg',
    );
    if (responseData instanceof Promise) {
      responseData.then((result) => {
        if (result.status !== undefined) {
          this.setState({
            openModal: true,
          });
        }
      });
    }
  };

  // Verify OTP
  verifyOtp = () => {
    const responseData = verifyOtp(
      `${document.getElementById('edit-field-mobile-number-0-mobile').value}`,
      `${document.getElementsByName('OTP')[0].value}`,
      'reg',
      `${drupalSettings.alshaya_mobile_prefix.slice(1)}`,
    );
    if (responseData instanceof Promise) {
      responseData.then((result) => {
        if (result.data.status !== undefined) {
          this.setState({
            openModal: false,
          });
        }
        // If successfully verified make the otp verified checkbox seleted.
        document.getElementById('edit-field-verified-otp-value').click();
      });
    }
  };

  render() {
    const { openModal } = this.state;
    return (
      <>
        <button onClick={this.openModal} type="button">{getStringMessage('send_otp_label')}</button>
        <Popup
          open={openModal}
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="hello-member-otp-popup-form">
            <a className="close-modal" onClick={(e) => this.closeModal(e)} />
            { getStringMessage('sent_otp_message') }
            { document.getElementById('edit-field-mobile-number-0-mobile').value }
            <input
              type="number"
              id="OTP"
              name="OTP"
              className="OTP"
              maxLength="6"
              max="999999"
            />
          </div>
          <div className="hello-member-modal-form-actions">
            <div className="hello-member-otp-submit-description">
              { getStringMessage('resend_otp_desc') }
              <div className="hello-member-modal-form-resend-otp" onClick={() => this.resendOtp()}>{ getStringMessage('resend_code_label') }</div>
            </div>
            <div className="hello-member-modal-form-submit" onClick={() => this.verifyOtp()}>{ getStringMessage('verify_label') }</div>
          </div>
        </Popup>
      </>
    );
  }
}

export default SendOtpPopup;
