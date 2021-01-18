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
import {
  getElementValueByType,
  getInlineErrorSelector,
  resetInputElement,
} from '../../utilities/link_card_sign_up_modal_helper';
import { validateMobile, validateElementValueByType } from '../../utilities/validation_helper';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import {
  showError,
  removeError,
  getElementValue,
} from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { handleManualLinkYourCard } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';

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

  componentDidMount() {
    const { closeLinkCardOTPModal } = this.props;
    document.addEventListener('loyaltyStatusUpdated', closeLinkCardOTPModal, false);
  }

  processLinkCardSendOtp = () => {
    this.resetModalMessages();
    const { linkCardOption } = this.state;
    const isValid = validateElementValueByType(linkCardOption);

    if (isValid === false) {
      return;
    }

    const selectedElementValue = getElementValueByType(linkCardOption);
    const { chosenCountryCode } = this.props;

    if (linkCardOption !== 'mobile') {
      this.setState({
        [linkCardOption]: selectedElementValue,
      });

      this.sendOtp({ type: linkCardOption, value: selectedElementValue });
      return;
    }

    const data = {
      mobile: selectedElementValue,
      chosenCountryCode,
    };
    const validationRequest = validateMobile(linkCardOption, data);

    if (validationRequest instanceof Promise) {
      validationRequest.then((valid) => {
        if (valid === false) {
          return;
        }
        removeError(getInlineErrorSelector(linkCardOption)[linkCardOption]);
        this.setState({
          [linkCardOption]: chosenCountryCode + selectedElementValue,
        });

        this.sendOtp({ type: linkCardOption, value: chosenCountryCode + selectedElementValue });
      });
    }
  };

  resetModalMessages = () => {
    // Reset/Remove if any message is displayed.
    this.setState({
      messageType: null,
      messageContent: null,
    });
  };

  // Send OTP to the user.
  sendOtp = (data) => {
    const { linkCardOption } = this.state;
    removeError(getInlineErrorSelector(linkCardOption)[linkCardOption]);
    resetInputElement('otp');
    const apiUrl = 'post/loyalty-club/send-link-card-otp';
    const apiData = postAPIData(apiUrl, data);
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
                mobile: result.data.mobile || null,
                cardNumber: result.data.cardNumber || null,
              });
              document.querySelector('.aura-form-items-link-card-options').classList.add('disabled');
            }
            removeFullScreenLoader();
            return;
          }

          showError(
            getInlineErrorSelector(linkCardOption)[linkCardOption],
            getStringMessage(result.data.error_message),
          );
          removeFullScreenLoader();
          return;
        }

        this.setState({
          messageType: 'error',
          messageContent: getStringMessage('form_error_send_otp_failed_message'),
        });
        removeFullScreenLoader();
      });
    }
  };

  verifyOtpAndLink = () => {
    this.resetModalMessages();
    const { mobile, cardNumber } = this.state;
    const otp = getElementValue('otp');

    if (otp.length === 0) {
      showError(getInlineErrorSelector('otp').otp, getStringMessage('form_error_otp'));
      return;
    }

    removeError(getInlineErrorSelector('otp').otp);
    handleManualLinkYourCard(cardNumber, mobile, otp);
  };

  selectOption = (option) => {
    // Reset input elements.
    resetInputElement();

    this.setState({
      linkCardOption: option,
      cardNumber: null,
      email: null,
      mobile: null,
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
      setChosenCountryCode,
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

    const submitButtonText = otpRequested === true ? Drupal.t('Link Now') : Drupal.t('Send one time PIN');

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
                    setChosenCountryCode={setChosenCountryCode}
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
                  onClick={() => this.processLinkCardSendOtp()}
                >
                  {getStringMessage('resend_code')}
                </span>
              </ConditionalView>
            </div>
            <ConditionalView condition={otpRequested === false}>
              <div className="aura-modal-form-submit" onClick={() => this.processLinkCardSendOtp()}>
                {submitButtonText}
              </div>
            </ConditionalView>
            <ConditionalView condition={otpRequested === true}>
              <div className="aura-modal-form-submit" onClick={() => this.verifyOtpAndLink()}>
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
