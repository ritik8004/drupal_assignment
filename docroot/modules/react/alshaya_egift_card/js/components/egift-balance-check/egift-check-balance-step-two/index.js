import React from 'react';
import Popup from 'reactjs-popup';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { sendOtp } from '../../../../../js/utilities/egiftCardHelper';
import PriceElement from '../../../../../js/utilities/components/price/price-element';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { getDefaultErrorMessage } from '../../../../../js/utilities/error';

export default class EgiftCheckBalanceStepTwo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      initialStep: props.initialStep, // Updated step.
      egiftCardBalance: 0, // eGift card balance.
      egiftCardvalidity: '', // eGift card validity.
    };
  }

  // Handling validation for egift card otp field.
  handleValidation = (e) => {
    const { value: egiftCardOtp } = e.target.elements.egift_card_otp;
    let errors = false;
    let message = '';
    // Egift card otp validation.
    if (egiftCardOtp.length === 0) {
      message = Drupal.t('Please enter otp.', {}, { context: 'egift' });
      errors = true;
    } else if (!egiftCardOtp.match(/^[a-z0-9A-Z]+$/i)) {
      // Check if the otp is valid or not.
      message = Drupal.t('Please enter valid otp.', {}, { context: 'egift' });
      errors = true;
    } else {
      message = '';
    }
    document.getElementById('egift_card_otp_error').innerHTML = message;
    return errors;
  }

  // Handle the form submit.
  handleSubmit = (e) => {
    e.preventDefault();
    const { value: egiftCardNumber } = e.target.elements.egift_card_number;
    const { initialStep } = this.state;

    // Perform validation.
    if (!this.handleValidation(e)) {
      if (initialStep === 2) {
        const { value: egiftCardOtp } = e.target.elements.egift_card_otp;
        const postData = {
          accountInfo: {
            cardNumber: egiftCardNumber,
            action: 'get_balance',
            otp: egiftCardOtp,
          },
        };
        // Show loader on api call.
        showFullScreenLoader();
        // Call get balance api.
        const BalanceResponse = callMagentoApi('/V1/egiftcard/getBalance', 'POST', postData);
        BalanceResponse.then((response) => {
          // Remove loader on api success.
          removeFullScreenLoader();
          if (response.status === 200) {
            if (response.data.response_type === true) {
              // Set the state to next level on api success.
              this.setState({
                initialStep: 3,
                egiftCardBalance: response.data.current_balance,
                egiftCardvalidity: response.data.expiry_date,
              });
            } else {
              // Show error message if api fails and dont proceed further.
              document.getElementById('egift_card_otp_error').innerHTML = response.data.response_message;
              logger.error('Error in getting users card balance api response. Action: @action CardNumber: @cardNumber Response: @response', {
                '@action': 'get_balance',
                '@cardNumber': egiftCardNumber,
                '@response': response.data.response_message,
              });
              return false;
            }
          } else {
            document.getElementById('egift_card_number_error').innerHTML = getDefaultErrorMessage();
          }
          return false;
        });
      }
    }
    return false;
  }

  // Handle re-send otp.
  handleResendCode = (e) => {
    e.preventDefault();
    const { egiftCardNumber } = this.state;
    // Show loader on api call.
    showFullScreenLoader();
    // Call send otp api.
    const OtpResponse = sendOtp(egiftCardNumber);
    OtpResponse.then((response) => {
      // Remove loader on api success.
      removeFullScreenLoader();
      if (response.status === 200) {
        if (response.data.response_type === true) {
          // Clear the otp field on api success.
          document.getElementById('egift_card_otp').value = '';
        } else {
          // Show error message if api fails and dont proceed further.
          document.getElementById('egift_card_otp_error').innerHTML = response.data.response_message;
          logger.error('Empty Response in sending opt for getting users card balance . Action: @action CardNumber: @cardNumber Response: @response', {
            '@action': 'get_balance',
            '@cardNumber': egiftCardNumber,
            '@response': response.data.response_message,
          });
          return false;
        }
      } else {
        document.getElementById('egift_card_number_error').innerHTML = getDefaultErrorMessage();
      }
      return false;
    });
    return false;
  }

  // Handle change another card.
  handleChangeCardNumber = () => {
    const { stepChange } = this.props;
    // Moves to initial step.
    stepChange(1);
    return false;
  }

  // Redirect to topup page.
  handleRedirect = () => {
    window.location.href = '/gift-card/topup-card';
    return false;
  }

  render = () => {
    const {
      initialStep,
      egiftCardBalance,
      egiftCardvalidity,
    } = this.state;
    const {
      cardNumber,
      closeModal,
      open,
    } = this.props;

    // Add buttons on api success response.
    const topupName = Drupal.t('TOP UP CARD', {}, { context: 'egift' });
    const buttonName = Drupal.t('CHECK ANOTHER CARD', {}, { context: 'egift' });
    const topupButton = React.createElement('button', { type: 'submit', onClick: this.handleRedirect }, topupName);
    const anotherCardButton = React.createElement('button', { type: 'submit', onClick: this.handleChangeCardNumber }, buttonName);

    return (
      <>
        <Popup
          open={open}
          className="egift-balance-check"
          onClose={closeModal}
          closeOnDocumentClick={false}
        >
          <div className="egift-amount-update-wrapper">
            <a className="close" onClick={() => closeModal()}> &times; </a>
            <div className="heading">{Drupal.t('Check Balance & Validity', {}, { context: 'egift' })}</div>
            <div className="form-wrapper">
              <ConditionalView condition={initialStep === 2}>
                <form
                  className="egift-balance-check-form"
                  method="post"
                  id="egift-balance-check-form"
                  onSubmit={this.handleSubmit}
                >
                  <div className="egift-header-wrapper">
                    <p>
                      <strong>{Drupal.t('Enter gift card details to check balance & validity', {}, { context: 'egift' })}</strong>
                    </p>
                  </div>
                  <div className="egift-type-card_number">
                    <input
                      type="text"
                      name="egift_card_number"
                      placeholder="eGift Card Number"
                      defaultValue={cardNumber}
                      className="card-number"
                      disabled="disabled"
                    />
                    <div id="egift_card_number_error" className="error" />
                  </div>
                  <div className="egift-type-card_otp">
                    <input
                      type="text"
                      name="egift_card_otp"
                      placeholder="eGift Card Otp"
                      className="card-otp"
                      id="egift_card_otp"
                    />
                    <div id="egift_card_otp_error" className="error" />
                  </div>
                  <div className="egift-type-resend_otp">
                    <span>{Drupal.t('Didn\'t receive?', {}, { context: 'egift' })}</span>
                    <span onClick={(e) => this.handleResendCode(e)}>
                      {Drupal.t('Resend Code', {}, { context: 'egift' })}
                    </span>
                  </div>
                  <div className="egift-type-another_card">
                    <span onClick={() => this.handleChangeCardNumber()}>
                      {Drupal.t('Change Card?', {}, { context: 'egift' })}
                    </span>
                  </div>
                  <input
                    className="egift-button"
                    id="egift-button"
                    type="submit"
                    value={Drupal.t('CHECK BALANCE', {}, { context: 'egift' })}
                  />
                </form>
              </ConditionalView>
              <ConditionalView condition={initialStep === 3}>
                <div className="egift-balance-response">
                  <p>
                    {Drupal.t('Here is your current balance', {}, { context: 'egift' })}
                  </p>
                  <strong>
                    <PriceElement amount={parseInt(egiftCardBalance, 10)} />
                  </strong>
                  <p>
                    {Drupal.t('for eGift card ending in ..', {}, { context: 'egift' })}
                    {cardNumber.slice(-4)}
                  </p>
                  <p>
                    {Drupal.t('Card valid up to ', {}, { context: 'egift' })}
                    {egiftCardvalidity}
                  </p>
                  { topupButton }
                  { anotherCardButton }
                </div>
              </ConditionalView>
            </div>
          </div>
        </Popup>
      </>
    );
  }
}
