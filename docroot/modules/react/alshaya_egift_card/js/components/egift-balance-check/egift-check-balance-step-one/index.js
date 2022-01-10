import React from 'react';
import Popup from 'reactjs-popup';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import EgiftCheckBalanceStepTwo from '../egift-check-balance-step-two';
import { sendOtp } from '../../../../../js/utilities/egiftCardHelper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';

export default class EgiftCheckBalanceStepOne extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      egiftCardNumber: '', // eGift card number.
    };
  }

  // Handling validation for egift card number.
  handleValidation = (e) => {
    const { value: egiftCardNumber } = e.target.elements.egift_card_number;
    let errors = false;
    let message = '';
    // Egift card number validation.
    if (egiftCardNumber.length === 0) {
      message = Drupal.t('Please enter card number.', {}, { context: 'egift' });
      errors = true;
    } else if (!egiftCardNumber.match(/^[a-z0-9A-Z]+$/i)) {
      // Check if the card number is valid or not.
      message = Drupal.t('Please enter valid card number.', {}, { context: 'egift' });
      errors = true;
    } else {
      message = '';
    }
    document.getElementById('egift_card_number_error').innerHTML = message;
    return errors;
  }

  // Handle the form submit.
  handleSubmit = (e) => {
    e.preventDefault();
    const { value: cardNumber } = e.target.elements.egift_card_number;
    const { initialStep, stepChange } = this.props;

    let OtpResponse = {};
    let message = '';
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
                this.setState({
                  egiftCardNumber: cardNumber,
                });
                // Update the step to next level on api success.
                stepChange(2);
              } else {
                // Update the error on api failure and dont proceed further.
                document.getElementById('egift_card_number_error').innerHTML = res.data.response_message;
                logger.error('Error in sending opt for getting users card balance response. Action: @action CardNumber: @cardNumber Response: @response', {
                  '@action': 'send_otp',
                  '@cardNumber': cardNumber,
                  '@response': res.data.response_message,
                });
                return false;
              }
            } else {
              message = Drupal.t('Something went wrong, please try again later.', {}, { context: 'egift' });
              document.getElementById('egift_card_number_error').innerHTML = message;
            }
            return false;
          });
        }
      }
    }
    return false;
  }

  render = () => {
    const {
      egiftCardNumber,
    } = this.state;
    const {
      closeModal,
      open,
      initialStep,
      stepChange,
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
            <a className="close" onClick={() => closeModal()}> &times; </a>
            <div className="heading">{Drupal.t('Check Balance & Validity', {}, { context: 'egift' })}</div>
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
                      <strong>{Drupal.t('Enter gift card details to check balance & validity.', {}, { context: 'egift' })}</strong>
                    </p>
                  </div>
                  <div className="egift-type-card_number">
                    <input
                      type="text"
                      name="egift_card_number"
                      placeholder="eGift Card Number"
                      className="card-number"
                    />
                    <div id="egift_card_number_error" className="error" />
                  </div>
                  <input
                    className="egift-button"
                    id="egift-button"
                    type="submit"
                    value={Drupal.t('CHECK BALANCE', {}, { context: 'egift' })}
                  />
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
  }
}
