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
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import {
  showError,
  removeError,
  getElementValue,
  getAuraCheckoutLocalStorageKey,
  getAuraLocalStorageKey,
} from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { handleManualLinkYourCard } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import { processCheckoutCart } from '../../utilities/checkout_helper';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { isUserAuthenticated } from '../../../../../../js/utilities/helper';
import { getAllAuraStatus } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

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

    /**
     * As part of https://alshayagroup.atlassian.net/browse/CORE-39968,
     * we have provided a popup to guest users to link the aura card with current basket.
     * Aura account based on provided details(mobile,email,card Number) will be searched,
     * After which 'loyaltyDetailsSearchComplete' event will be dispatched,
     * with searched Aura card details.
     * We have added the eventListener to trigger 'handleSearchEvent' method,
     * which will show errors if any, or will add aura card details to localStorage.
     */
    document.addEventListener('loyaltyDetailsSearchComplete', this.handleSearchEvent, false);
  }

  /**
   * Remove the event listener when component gets deleted.
   */
  componentWillUnmount() {
    const { closeLinkCardOTPModal } = this.props;
    document.removeEventListener('loyaltyStatusUpdated', closeLinkCardOTPModal, false);
    document.removeEventListener('loyaltyDetailsSearchComplete', this.handleSearchEvent, false);
  }

  /**
   * Method gets invoked by 'loyaltyDetailsSearchComplete' Event Listener.
   *
   * Will show respective errors if no account exists with the details provided,
   * Or add aura card details to local storage if it exists.
   */
  handleSearchEvent = (data) => {
    const { stateValues, searchData } = data.detail;
    const { linkCardOption } = this.state;
    const { closeLinkCardOTPModal } = this.props;

    // Check if there is any error available.
    if (stateValues.error) {
      /**
       * Show error/Validation message below the respective html element if there is any.
       * For ex, IF there is no aura card linked with provided mobile number.
       * wrong mobile number message will be shown below mobile field.
       */
      showError(
        getInlineErrorSelector(linkCardOption)[linkCardOption],
        getStringMessage(stateValues.error_message),
      );
      return;
    }

    // Proceed only if search data is returned by API.
    if (searchData) {
      // Fetching cart_id From localstorage.
      // Below code never gets called for authenticated user.
      const cartId = Drupal.getItemFromLocalStorage('cart_id');

      // Don't process further if no cart id is available.
      if (hasValue(cartId)) {
        // Set aura checkout local storage.
        Drupal.addItemInLocalStorage(
          getAuraCheckoutLocalStorageKey(),
          { cartId, ...searchData },
        );
      }

      // Set available data into current state.
      this.setState({
        ...stateValues,
      });

      // Close link card Otp Modal.
      closeLinkCardOTPModal();

      // Update Loyalty status, Set it to 'APC_LINKED_NOT_VERIFIED',
      // If not set or it is 'APC_NOT_LINKED_DATA' as there might be cases when
      // Aura account exists but not linked to any MDC account.
      stateValues.loyaltyStatus = stateValues.loyaltyStatus
      && parseInt(stateValues.loyaltyStatus, 10) !== getAllAuraStatus().APC_NOT_LINKED_DATA
        ? parseInt(stateValues.loyaltyStatus, 10)
        : getAllAuraStatus().APC_LINKED_NOT_VERIFIED;

      // Add aura data to local starage.
      Drupal.addItemInLocalStorage(
        getAuraLocalStorageKey(),
        stateValues,
      );

      // Dispatch loyaltyStatusUpdated Event and send Aura data along with it,
      // To take further actions such as showing congratulations popup.
      dispatchCustomEvent('loyaltyStatusUpdated', {
        // If showCongratulationsPopup passed true,
        // It will open congratulations popup.
        showCongratulationsPopup: true,
        stateValues,
      });
    }
  };

  processLinkCardSendOtp = () => {
    this.resetModalMessages();
    removeError(getInlineErrorSelector('otp').otp);
    const { linkCardOption } = this.state;
    const isValid = validateElementValueByType(linkCardOption, '.aura-modal-form', 'link_card');

    if (!isValid) {
      return;
    }

    const selectedElementValue = getElementValueByType(linkCardOption, '.aura-modal-form');
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
        if (!valid) {
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

    const apiData = window.auraBackend.sendLinkCardOtp(data.type, data.value);
    showFullScreenLoader();

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined) {
          if (result.data.error === undefined) {
            // Once we get a success response that OTP is sent, we update state,
            // to show the otp fields.
            if (result.data.status) {
              // Disable aura link card options, if available.
              const cardOptionsLink = document.querySelector('.aura-form-items-link-card-options');
              if (cardOptionsLink !== null) {
                cardOptionsLink.classList.add('disabled');
              }

              this.setState({
                otpRequested: true,
                mobile: result.data.mobile || null,
                cardNumber: result.data.cardNumber || null,
              });
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
    resetInputElement('all', '.aura-modal-form');

    this.setState({
      linkCardOption: option,
      cardNumber: null,
      email: null,
      mobile: null,
    });
  };

  // Adds Aura Card to cart.
  addCard = () => {
    this.resetModalMessages();
    const { linkCardOption } = this.state;
    const { chosenCountryCode, clickedOnNotYou } = this.props;

    // Check if Field contains valid value.
    const isValid = validateElementValueByType(linkCardOption, '.aura-modal-form', 'link_card');
    if (!isValid) {
      return;
    }

    // Format data to send to Set Loyalty card api.
    const selectedElementValue = getElementValueByType(linkCardOption, '.aura-modal-form');
    const fieldData = {
      type: linkCardOption,
      value: selectedElementValue,
    };
    if (linkCardOption === 'mobile') {
      fieldData.countryCode = chosenCountryCode;
      fieldData.type = 'phone';
    }
    fieldData.action = 'add';
    fieldData.gtmLinkCardOption = linkCardOption === 'cardNumber' ? 'a/c number' : linkCardOption;
    fieldData.clickedOnNotYou = clickedOnNotYou;
    showFullScreenLoader();

    // Send fieldData to set loyalty card to current cart.
    processCheckoutCart(fieldData);
  };


  render() {
    const {
      closeLinkCardOTPModal,
      setChosenCountryCode,
      openOTPModal,
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

    // Get Authentication status in a variable,
    // As we are using on multiple places.
    const isUserLoggedIn = isUserAuthenticated();
    const submitButtonText = otpRequested
      ? getStringMessage('aura_link_now')
      : getStringMessage('aura_send_otp');

    return (
      <div className="aura-guest-user-link-card-otp-form">
        <div className="aura-modal-header">
          <SectionTitle>
            {isUserLoggedIn
              ? getStringMessage('aura_link_aura')
              : getStringMessage('link_card_header_guest')}
          </SectionTitle>
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
            <ConditionalView condition={!otpRequested}>
              <div className="linkingoptions-label">
                {isUserLoggedIn
                  ? getStringMessage('link_card_body_title_logged_in')
                  : [
                    <span key="title">{getStringMessage('link_card_body_title_guest')}</span>,
                    <span key="sub_title">{getStringMessage('link_card_body_sub_title_guest')}</span>,
                  ]}
              </div>
            </ConditionalView>
            { otpRequested && (
              <div className="otp-sent-to-mobile-label">
                <span>
                  {getStringMessage('aura_otp_sent_to_mobile', {
                    '@mobile': mobile,
                  })}
                </span>
              </div>
            )}

            <ConditionalView condition={!otpRequested}>
              <AuraFormLinkCardOptions
                selectedOption={linkCardOption}
                selectOptionCallback={this.selectOption}
                cardNumber={cardNumber}
              />
            </ConditionalView>
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
                <ConditionalView condition={otpRequested}>
                  <TextField
                    type="text"
                    required={false}
                    name="otp"
                    label={getStringMessage('aura_otp_label')}
                  />
                </ConditionalView>
              </div>
              <ConditionalView condition={window.innerWidth < 768}>
                <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
              </ConditionalView>
            </div>
          </div>
          <div className="aura-modal-form-actions">
            {/* Apply button will be visible only for guest users. */}
            <ConditionalView condition={!isUserLoggedIn}>
              <div className="aura-modal-form-submit-without-otp" onClick={() => this.addCard()}>
                {/* Clicking on apply button will set card to basket. */}
                {Drupal.t('Apply', {}, { context: 'aura' })}
              </div>
            </ConditionalView>
            {/* OTP and link now button only be visible for Signed in Users. */}
            <ConditionalView condition={isUserLoggedIn}>
              <div className="aura-new-user-t-c aura-otp-submit-description">
                <ConditionalView condition={otpRequested}>
                  <span key="part1" className="part">{getStringMessage('didnt_recieve_the_otp_message')}</span>
                  <span
                    className="resend-otp"
                    onClick={() => this.processLinkCardSendOtp()}
                  >
                    {getStringMessage('aura_resend_code')}
                  </span>
                </ConditionalView>
              </div>
              <ConditionalView condition={!otpRequested}>
                <div className="aura-modal-form-submit" onClick={() => this.processLinkCardSendOtp()}>
                  {submitButtonText}
                </div>
              </ConditionalView>
              <ConditionalView condition={otpRequested}>
                <div className="aura-modal-form-submit" onClick={() => this.verifyOtpAndLink()}>
                  {submitButtonText}
                </div>
              </ConditionalView>
            </ConditionalView>
            <div className="aura-modal-footer">
              <div
                className="join-aura"
                onClick={() => {
                  openOTPModal();
                  Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_UP', label: 'initiated' });
                }}
              >
                {getStringMessage('aura_join_aura')}
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormLinkCardOTPModal;
