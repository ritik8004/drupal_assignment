import React from 'react';
import Popup from 'reactjs-popup';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import EgiftCheckBalanceStepTwo from '../egift-check-balance-step-two';
import { sendOtp } from '../../../../../js/utilities/egiftCardHelper';

export default class EgiftCheckBalanceStepOne extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      egiftCardNumber: '', // eGift card number.
    };
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
    const { value: egiftCardNumber } = e.target.elements.egift_card_number;
    let errors = false;
    let message = '';
    // Egift card number validation.
    if (egiftCardNumber.length === 0) {
      message = Drupal.t('Please enter card number.', {}, { context: 'egift' });
      errors = true;
    } else if (!egiftCardNumber.match(/^[a-z0-9A-Z]+$/i)) {
      // Check if the card number is valid or not.
      message = Drupal.t(
        'Please enter valid card number.',
        {},
        { context: 'egift' },
      );
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
    const { value: cardNumber } = e.target.elements.egift_card_number;
    const { initialStep, stepChange } = this.props;

    let OtpResponse = {};
    // Perform validation.
    if (!this.handleValidation(e)) {
      if (initialStep === 1) {
        OtpResponse = sendOtp(cardNumber);
        if (OtpResponse instanceof Promise) {
          OtpResponse.then((res) => {
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
            }
            return false;
          });
        }
      }
    }
    return false;
  };

  render = () => {
    const { egiftCardNumber } = this.state;
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
            <a className="close" onClick={() => closeModal()}>
              {' '}
              &times;
              {' '}
            </a>
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
                      <strong>
                        {Drupal.t(
                          'Enter gift card details to check balance & validity.',
                          {},
                          { context: 'egift' },
                        )}
                      </strong>
                    </p>
                  </div>
                  <div className="egift-type-card_number">
                    <input
                      type="text"
                      name="egift_card_number"
                      className="card-number"
                      onBlur={(e) => this.handleEvent(e)}
                    />
                    <div className="c-input__bar" />
                    <label>
                      {Drupal.t('eGift Card Number*', {}, { context: 'egift' })}
                    </label>
                    <div id="egift_card_number_error" className="error" />
                  </div>
                  <div className="egift-topup-btn-wrapper">
                    <input
                      className="egift-button"
                      id="egift-button"
                      type="submit"
                      value={Drupal.t(
                        'CHECK BALANCE',
                        {},
                        { context: 'egift' },
                      )}
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
