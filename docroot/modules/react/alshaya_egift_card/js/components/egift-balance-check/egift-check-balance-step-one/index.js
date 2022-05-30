import React from 'react';
import Popup from 'reactjs-popup';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import EgiftCheckBalanceStepTwo from '../egift-check-balance-step-two';
import {
  allowWholeNumbers,
  sendOtp,
} from '../../../../../js/utilities/egiftCardHelper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { getDefaultErrorMessage } from '../../../../../js/utilities/error';
import { isEgiftCardEnabled } from '../../../../../js/utilities/util';

export default class EgiftCheckBalanceStepOne extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  handleEvent = (e) => {
    if (e.currentTarget.value.length > 0) {
      e.currentTarget.classList.add('focus');
    } else {
      e.currentTarget.classList.remove('focus');
    }
  };

  // Handling validation for egift card number.
  handleValidation = (e) => {
    const { value } = e.target.elements.egift_card_number;
    const egiftCardNumber = value.trim();
    let errors = false;
    let message = '';
    // Egift card number validation.
    if (egiftCardNumber.length === 0) {
      message = Drupal.t('Please enter an eGift card number.', {}, { context: 'egift' });
      errors = true;
    } else if (!egiftCardNumber.match(/^[a-z0-9A-Z]+$/i)) {
      // Check if the card number is valid or not.
      message = Drupal.t('Please enter a valid eGift card number.', {}, { context: 'egift' });
      errors = true;
    } else {
      message = '';
    }
    document.getElementById('egift_card_number_error').innerHTML = message;
    return errors;
  };

  // Handle the form submit.
  handleSubmit = (e) => {
    e.preventDefault();
    const { value } = e.target.elements.egift_card_number;
    const cardNumber = value.trim();
    const { initialStep, stepChange } = this.props;

    let OtpResponse = {};
    // Perform validation.
    if (!this.handleValidation(e)) {
      if (initialStep === 1) {
        // Show loader on api call.
        showFullScreenLoader();
        OtpResponse = sendOtp(cardNumber);
        if (OtpResponse instanceof Promise) {
          OtpResponse.then((res) => {
            // Remove loader on api success.
            removeFullScreenLoader();
            if (res.status === 200) {
              if (res.data.response_type === true) {
                // Update the step to next level on api success.
                stepChange(2, cardNumber);
              } else {
                // Update the error on api failure and dont proceed further.
                document.getElementById('egift_card_number_error').innerHTML = res.data.response_message;
                logger.error(
                  'Error in sending opt for getting users card balance response. Action: @action CardNumber: @cardNumber Response: @response',
                  {
                    '@action': 'send_otp',
                    '@cardNumber': cardNumber,
                    '@response': res.data.response_message,
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
    }
    return false;
  };

  render = () => {
    if (!isEgiftCardEnabled()) {
      // Do not show popup if eGift is not enabled.
      return (null);
    }

    const { egiftCardNumber } = this.props;
    const {
      closeModal, open, initialStep, stepChange,
    } = this.props;
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
              <ConditionalView condition={initialStep === 1}>
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
                      className={egiftCardNumber !== '' ? 'card-number focus' : 'card-number'}
                      onBlur={(e) => this.handleEvent(e)}
                      defaultValue={egiftCardNumber}
                      onInput={(e) => allowWholeNumbers(e)}
                    />
                    <div className="c-input__bar" />
                    <label>
                      {Drupal.t('eGift Card Number', {}, { context: 'egift' })}
                    </label>
                    <div id="egift_card_number_error" className="error" />
                  </div>
                  <div className="egift-topup-btn-wrapper">
                    <input
                      className="egift-button"
                      id="egift-button"
                      type="submit"
                      value={Drupal.t('CHECK BALANCE', {}, { context: 'egift' })}
                    />
                  </div>
                </form>
              </ConditionalView>
              <ConditionalView condition={initialStep === 2}>
                <EgiftCheckBalanceStepTwo
                  closeModal={closeModal}
                  open={open}
                  cardNumber={egiftCardNumber}
                  initialStep={initialStep}
                  stepChange={stepChange}
                />
              </ConditionalView>
            </div>
          </div>
        </Popup>
      </>
    );
  };
}
