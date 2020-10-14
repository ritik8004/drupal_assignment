import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import ConditionalView from '../../../../common/components/conditional-view';

class AuraFormSignUpOTPModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otpRequested: false,
    };
  }

  requestOtp = () => {
    // @todo: API Call to request OTP for the mobile number.
    // Once we get a success response that OTP is sent, we update state,
    // to show the otp fields.
    this.setState({
      otpRequested: true,
    });
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

    const submitButtonText = otpRequested === true ? Drupal.t('Verify') : Drupal.t('Send One Time Pin');

    return (
      <div className="aura-otp-form">
        <div className="aura-modal-header">
          <SectionTitle>{Drupal.t('Say hello to Aura')}</SectionTitle>
          <a className="close" onClick={() => closeModal()} />
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
            <div className="aura-modal-form-submit" onClick={() => this.requestOtp()}>{submitButtonText}</div>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormSignUpOTPModal;
