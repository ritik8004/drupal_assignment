import React from 'react';
import Popup from 'reactjs-popup';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import AuraFormNewAuraUserModal from '../aura-new-aura-user-form';

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

  requestOtp = () => {
    const {
      otpRequested,
    } = this.state;

    // If state is true, it means otp is already sent.
    if (otpRequested === false) {
      // @todo: API Call to request OTP for the mobile number.
      // Once we get a success response that OTP is sent, we update state,
      // to show the otp fields.
      this.setState({
        mobileNumber: document.querySelector('#otp-mobile-number').value,
        otpRequested: true,
      });
    }
  };

  verifyOtp = () => {
    const {
      otpRequested,
    } = this.state;

    // If state is true, it means otp is already sent.
    if (otpRequested === true) {
      // @todo: API Call to verify OTP for the mobile number.
      // Open modal for the new user.
      this.openNewUserModal();
    }
  };

  getOtpDescription = () => {
    const {
      otpRequested,
    } = this.state;

    let description = '';
    if (otpRequested === true) {
      description = [
        <span key="part1" className="part">{Drupal.t('We have sent the One Time Pin to your mobile number.')}</span>,
        <span key="part2" className="part">{Drupal.t('Didn’t receive the One Time Pin?')}</span>,
      ];
    } else {
      description = Drupal.t('We will send a One Time Pin to your both your email address and mobile number.');
    }
    return description;
  };

  render() {
    const {
      closeOTPModal,
    } = this.props;

    const {
      otpRequested,
      mobileNumber,
      isNewUserModalOpen,
    } = this.state;

    const submitButtonText = otpRequested === true ? Drupal.t('Verify') : Drupal.t('Send One Time Pin');

    return (
      <div className="aura-otp-form">
        <div className="aura-modal-header">
          <SectionTitle>{Drupal.t('Say hello to Aura')}</SectionTitle>
          <button type="button" className="close" onClick={() => closeOTPModal()} />
        </div>
        <div className="aura-modal-form">
          <div className="aura-modal-form-items">
            <TextField
              type="text"
              required
              name="otp-mobile-number"
              label={Drupal.t('Mobile Number')}
            />
            <ConditionalView condition={otpRequested === true}>
              <TextField
                type="text"
                required={false}
                name="otp"
                label={Drupal.t('One Time Pin')}
              />
            </ConditionalView>
          </div>
          <div className="aura-modal-form-actions">
            <div className="aura-otp-submit-description">
              {this.getOtpDescription()}
              <ConditionalView condition={otpRequested === true}>
                <span className="resend-otp">{Drupal.t('Resend Code')}</span>
              </ConditionalView>
            </div>
            <ConditionalView condition={otpRequested === false}>
              <div className="aura-modal-form-submit" onClick={() => this.requestOtp()}>{submitButtonText}</div>
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
