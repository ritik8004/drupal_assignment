import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import AuraFormModalMessage from '../aura-form-modal-message';
import AuraFormLinkCardOptions from '../aura-form-link-card-options';
import getStringMessage from '../../../../utilities/strings';
import ConditionalView from '../../../../common/components/conditional-view';
import LinkCardOptionEmail
  from '../aura-link-card-textbox/components/link-card-option-email';
import LinkCardOptionCard
  from '../aura-link-card-textbox/components/link-card-option-card';
import LinkCardOptionMobile
  from '../aura-link-card-textbox/components/link-card-option-mobile';
import TextField from '../../../../utilities/textfield';

class AuraFormLinkCardOTPModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otpRequested: false,
      messageType: null,
      messageContent: null,
      cardNumber: null,
      email: null,
      mobile: null,
      linkCardOption: 'cardNumber',
    };
  }

  // Send OTP to the user.
  sendOtp = () => {
    // @todo: send otp.
    this.setState({
      otpRequested: true,
    });
  };

  verifyOtp = () => {
    const {
      closeLinkCardOTPModal,
    } = this.props;

    // @todo: verify otp and link card.
    // Close Modal.
    closeLinkCardOTPModal();
  };

  setChosenCountryCode = () => {
    // @TODO: The mobile field component needs this.
  };

  selectOption = (option) => {
    this.setState({
      linkCardOption: option,
    });
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
      closeLinkCardOTPModal,
    } = this.props;

    const {
      otpRequested,
      messageType,
      messageContent,
      cardNumber,
      email,
      mobile,
      linkCardOption,
    } = this.state;

    const submitButtonText = otpRequested === true ? Drupal.t('Link Now') : Drupal.t('Send One Time Pin');

    return (
      <div className="aura-guest-user-link-card-otp-form">
        <div className="aura-modal-header">
          <SectionTitle>{Drupal.t('Link Your Card')}</SectionTitle>
          <button type="button" className="close" onClick={() => closeLinkCardOTPModal()} />
        </div>
        <div className="aura-modal-form">
          <div className="aura-modal-form-items">
            <div className="aura-form-messages-container">
              <AuraFormModalMessage
                messageType={messageType}
                messageContent={messageContent}
              />
            </div>
            <div className="linkingoptions-label">{`${Drupal.t('Link card using')}:`}</div>
            <AuraFormLinkCardOptions
              selectedOption={linkCardOption}
              selectOptionCallback={this.selectOption}
              cardNumber={cardNumber}
            />
            <div className="spc-aura-link-card-wrapper">
              <div className="form-items">
                <ConditionalView condition={linkCardOption === 'email'}>
                  <LinkCardOptionEmail
                    modal
                    email={email}
                  />
                </ConditionalView>
                <ConditionalView condition={linkCardOption === 'cardNumber'}>
                  <LinkCardOptionCard
                    modal
                    cardNumber={cardNumber}
                  />
                </ConditionalView>
                <ConditionalView condition={linkCardOption === 'mobile'}>
                  <LinkCardOptionMobile
                    setChosenCountryCode={this.setChosenCountryCode}
                    mobile={mobile}
                  />
                </ConditionalView>
                <ConditionalView condition={otpRequested === true}>
                  <TextField
                    type="text"
                    required={false}
                    name="otp"
                    label={getStringMessage('otp_label')}
                  />
                </ConditionalView>
              </div>
              <ConditionalView condition={window.innerWidth < 768}>
                <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
              </ConditionalView>
            </div>
          </div>
          <div className="aura-modal-form-actions">
            <div className="aura-new-user-t-c aura-otp-submit-description">
              {this.getOtpDescription()}
              <ConditionalView condition={otpRequested === true}>
                <span
                  className="resend-otp"
                  onClick={() => this.sendOtp()}
                >
                  {getStringMessage('resend_code')}
                </span>
              </ConditionalView>
            </div>
            <ConditionalView condition={otpRequested === false}>
              <div className="aura-modal-form-submit" onClick={() => this.sendOtp()}>
                {submitButtonText}
              </div>
            </ConditionalView>
            <ConditionalView condition={otpRequested === true}>
              <div className="aura-modal-form-submit" onClick={() => this.verifyOtp()}>
                {submitButtonText}
              </div>
            </ConditionalView>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormLinkCardOTPModal;
