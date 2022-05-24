import React from 'react';
import Popup from 'reactjs-popup';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { sendOtp } from '../../../../../js/utilities/egiftCardHelper';
import PriceElement
  from '../../../../../alshaya_spc/js/utilities/special-price/PriceElement';
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

  handleEvent = (e) => {
    if (e.currentTarget.value.length > 0) {
      e.currentTarget.classList.add('focus');
    } else {
      e.currentTarget.classList.remove('focus');
    }
  };

  // Handling validation for egift card otp field.
  handleValidation = (e) => {
    const { value: egiftCardOtp } = e.target.elements.egift_card_otp;
    let errors = false;
    let message = '';
    // Egift card otp validation.
    if (egiftCardOtp.length === 0) {
      message = Drupal.t('Please enter verification code.', {}, { context: 'egift' });
      errors = true;
    } else if (!egiftCardOtp.match(/^[a-z0-9A-Z]+$/i)) {
      // Check if the otp is valid or not.
      message = Drupal.t('Please enter a valid verification code.', {}, { context: 'egift' });
      errors = true;
    } else {
      message = '';
    }
    document.getElementById('egift_card_otp_error').innerHTML = message;
    return errors;
  };

  // Handle the form submit.
  handleSubmit = (e) => {
    e.preventDefault();
    const { value } = e.target.elements.egift_card_number;
    const egiftCardNumber = value.trim();
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
        const BalanceResponse = callMagentoApi(
          '/V1/egiftcard/getBalance',
          'POST',
          postData,
        );
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
              logger.error(
                'Error in getting users card balance api response. Action: @action CardNumber: @cardNumber Response: @response',
                {
                  '@action': 'get_balance',
                  '@cardNumber': egiftCardNumber,
                  '@response': response.data.response_message,
                },
              );
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
  };

  // Handle re-send otp.
  handleResendCode = (e) => {
    e.preventDefault();
    const { cardNumber: egiftCardNumber } = this.props;
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
          logger.error(
            'Empty Response in sending opt for getting users card balance . Action: @action CardNumber: @cardNumber Response: @response',
            {
              '@action': 'get_balance',
              '@cardNumber': egiftCardNumber,
              '@response': response.data.response_message,
            },
          );
          return false;
        }
      } else {
        document.getElementById('egift_card_number_error').innerHTML = getDefaultErrorMessage();
      }
      return false;
    });
    return false;
  };

  // Handle change another card.
  handleChangeCardNumber = () => {
    const { stepChange, cardNumber } = this.props;
    // Moves to initial step.
    stepChange(1, cardNumber);
    return false;
  };

  // Handle check another card.
  handleCheckAnotherCard = () => {
    const { stepChange } = this.props;
    // Moves to initial step.
    stepChange(1);
    return false;
  };

  // Redirect to topup page.
  handleRedirect = () => {
    window.location.href = Drupal.url('egift-card/topup');
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
    const anotherCardButton = React.createElement('button', { type: 'submit', onClick: this.handleCheckAnotherCard }, buttonName);

    return (
      <>
        <Popup
          open={open}
          className="egift-balance-check"
          onClose={closeModal}
          closeOnDocumentClick={false}
        >
          <div className="egift-amount-update-wrapper">
            <div className="egift-check-bal-title">
              {Drupal.t('Check Balance & Validity', {}, { context: 'egift' })}
            </div>
            <a className="close" onClick={() => closeModal()}> &times; </a>
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
                      {Drupal.t(
                        'Enter gift card details to check balance & validity.',
                        {},
                        { context: 'egift' },
                      )}
                    </p>
                  </div>
                  <div className="egift-type-card_number">
                    <input
                      type="text"
                      name="egift_card_number"
                      defaultValue={cardNumber}
                      className="card-number"
                      disabled="disabled"
                      onBlur={(e) => this.handleEvent(e)}
                    />
                    <div className="c-input__bar" />
                    <label>
                      {Drupal.t('eGift Card Number', {}, { context: 'egift' })}
                    </label>
                    <div id="egift_card_number_error" className="error" />
                  </div>
                  <div className="egift-type-card_otp">
                    <input
                      type="text"
                      name="egift_card_otp"
                      className="card-otp"
                      id="egift_card_otp"
                      onBlur={(e) => this.handleEvent(e)}
                    />
                    <div className="c-input__bar" />
                    <label>
                      {Drupal.t('Enter verification code', {}, { context: 'egift' })}
                    </label>
                    <div id="egift_card_otp_error" className="error" />
                  </div>
                  <div className="egift-topup-btn-wrapper">
                    <div className="egift-code-wrapper">
                      <div className="egift-type-resend_otp">
                        <span>
                          {Drupal.t(
                            "Didn't receive?",
                            {},
                            { context: 'egift' },
                          )}
                        </span>
                        <span onClick={(e) => this.handleResendCode(e)}>
                          {Drupal.t('Resend Code', {}, { context: 'egift' })}
                        </span>
                      </div>
                      <div className="egift-type-another_card">
                        <span onClick={() => this.handleChangeCardNumber()}>
                          {Drupal.t('Change Card?', {}, { context: 'egift' })}
                        </span>
                      </div>
                    </div>
                    <input
                      className="egift-button"
                      id="egift-button"
                      type="submit"
                      value={Drupal.t('CHECK BALANCE', {}, { context: 'egift' })}
                    />
                  </div>
                </form>
              </ConditionalView>
              <ConditionalView condition={initialStep === 3}>
                <div className="egift-balance-response">
                  <p className="egift-current-balance-text">
                    {Drupal.t(
                      'Here is your current balance',
                      {},
                      { context: 'egift' },
                    )}
                  </p>
                  <p className="egift-price-text">
                    <PriceElement amount={parseFloat(egiftCardBalance, 10)} showZeroValue />
                  </p>
                  <p className="egift-card-end-text">
                    {Drupal.t(
                      'for eGift card ending in ..',
                      {},
                      { context: 'egift' },
                    )}
                    {cardNumber.slice(-4)}
                  </p>
                  <p className="egift-valid-text">
                    {Drupal.t('Card valid up to ', {}, { context: 'egift' })}
                    {egiftCardvalidity}
                  </p>
                  <div className="egift-topup-btn-wrapper">
                    {topupButton}
                    {anotherCardButton}
                  </div>
                </div>
              </ConditionalView>
            </div>
          </div>
        </Popup>
      </>
    );
  };
}
