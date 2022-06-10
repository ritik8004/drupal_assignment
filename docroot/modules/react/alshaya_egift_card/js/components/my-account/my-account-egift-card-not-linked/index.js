import React from 'react';
import ConditionalView
  from '../../../../../js/utilities/components/conditional-view';
import logger from '../../../../../js/utilities/logger';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import {
  allowWholeNumbers,
  callEgiftApi,
} from '../../../../../js/utilities/egiftCardHelper';

class EgiftCardNotLinked extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      enableVerifyCode: false,
      action: '',
    };
  }

  handleEvent = (e) => {
    if (e.currentTarget.value.length > 0) {
      e.currentTarget.classList.add('focus');
    } else {
      e.currentTarget.classList.remove('focus');
    }
  };

  /**
   * Get opt code for card number verification.
   */
  getOtpCode = (cardNumber) => {
    // Prepare params for send otp api.
    const params = {
      link_data: {
        action: 'send_otp',
        card_number: cardNumber,
      },
    };
    return callEgiftApi('eGiftLinkCard', 'POST', params)
      .then((response) => {
        removeFullScreenLoader();
        // Check for error from handleResponse.
        if (typeof response.data !== 'undefined' && typeof response.data.error !== 'undefined' && response.data.error) {
          document.getElementById('egift-card-number-error').innerHTML = response.data.error_message;
          logger.error('Error while unlinking card. @error', { '@error': JSON.stringify(response.data) });
          return false;
        }

        // Check error from magento
        if (typeof response.data !== 'undefined' && response.data.response_type === false) {
          document.getElementById('egift-card-number-error').innerHTML = response.data.response_message;
          logger.error('Error while unlinking card. @error', { '@error': JSON.stringify(response.data) });
          return false;
        }

        // If response is true, otp is send, show verify otp fields.
        if (typeof response.data !== 'undefined' && response.data.response_type) {
          this.setState({
            enableVerifyCode: true,
          });
        }
        return true;
      });
  }

  /**
   * Handle resend otp code action.
   */
  handleResendCode = (e) => {
    e.preventDefault();
    // Empty otp field.
    document.getElementsByName('otp-code')[0].value = '';
    showFullScreenLoader();
    const cardNumber = document.getElementsByClassName('egift-card-number')[0].value;
    this.getOtpCode(cardNumber).then((response) => {
      if (typeof response !== 'undefined' && response) {
        this.setState({
          enableVerifyCode: true,
        });
      }
    });
  }

  /**
   * Handle card number change action.
   */
  handleChangeCardNumber = (e) => {
    e.preventDefault();
    const { handleCardChange } = this.props;
    this.setState({
      enableVerifyCode: false,
    }, () => handleCardChange());
  }

  /**
   * Clear validations errors on focus or submit.
   */
  clearErrors = () => {
    document.querySelectorAll('.error').forEach((item) => {
      const element = item;
      element.innerHTML = '';
    });
  }

  /**
   * Handle link card form submit.
   */
  handleSubmit = (e) => {
    e.preventDefault();

    // Clear form validations errors before submit.
    this.clearErrors();
    // Get form data.
    const data = new FormData(e.target);
    const cardNumber = data.get('egift-card-number').trim();

    // Validate Card number.
    if (cardNumber === '') {
      // If card number is empty show error.
      document.getElementById('egift-card-number-error').innerHTML = Drupal.t('Please enter an eGift card number.', {}, { context: 'egift' });
      return;
    }

    // If card number is non english show error.
    if (!cardNumber.match(/^[a-z0-9A-Z]+$/i)) {
      document.getElementById('egift-card-number-error').innerHTML = Drupal.t('Please enter a valid eGift card number.', {}, { context: 'egift' });
      return;
    }

    // Get action.
    const { action } = this.state;
    switch (action) {
      case 'verifyOtp': {
        // Get Otp from the field.
        const otp = data.get('otp-code');
        if (otp === '') {
          // If otp is empty show error.
          document.getElementById('egift-code-error').innerHTML = Drupal.t('Please enter verification code.', {}, { context: 'egift' });
          return;
        }

        // If otp is non english show error.
        if (!otp.match(/^[a-z0-9A-Z]+$/i)) {
          document.getElementById('egift-code-error').innerHTML = Drupal.t('Please enter a valid verification code.', {}, { context: 'egift' });
          return;
        }

        showFullScreenLoader();

        // Verify otp.
        const params = {
          link_data: {
            action: 'verify_otp_link',
            card_number: cardNumber,
            otp,
          },
        };
        callEgiftApi('eGiftLinkCard', 'POST', params).then((response) => {
          // Check for error from handleResponse.
          if (typeof response.data !== 'undefined' && typeof response.data.error !== 'undefined' && response.data.error) {
            document.getElementById('egift-code-error').innerHTML = response.data.error_message;
            logger.error('Error while verifying otp. @error', { '@error': JSON.stringify(response.data) });
            removeFullScreenLoader();
            return false;
          }

          // Check for error from magento.
          if (typeof response.data !== 'undefined' && response.data.response_type === false) {
            document.getElementById('egift-code-error').innerHTML = response.data.response_message;
            logger.error('Error while verifying otp. @error', { '@error': JSON.stringify(response.data) });
            removeFullScreenLoader();
            return false;
          }

          // If response is true, otp is verified, link card to the customer.
          if (typeof response.data !== 'undefined' && response.data.response_type) {
            const { showCard } = this.props;
            showCard();
          }
          return true;
        });

        break;
      }
      default: {
        showFullScreenLoader();
        this.getOtpCode(cardNumber);
      }
    }
  }

  render() {
    const { enableVerifyCode } = this.state;

    return (
      <div className="egift-notlinked-wrapper">
        <div className="egift-notlinked-title">{Drupal.t('Link eGift card to account', {}, { context: 'egift' })}</div>
        <div className="egift-link-card-text">
          {
            Drupal.t('You dont have any eGift card linked to your account, link card to use it for your purchases', {}, { context: 'egift' })
          }
        </div>
        <ConditionalView condition={enableVerifyCode === false}>
          <div className="egift-link-card-instruction" id="resend-success">
            {
              Drupal.t('We\'ll send a verification code to your email to verify and link eGift card', {}, { context: 'egift' })
            }
          </div>
        </ConditionalView>
        <ConditionalView condition={enableVerifyCode}>
          <div className="egift-link-card-instruction" id="resend-success">
            {
              Drupal.t('Verification code is send to email address registered with the card number.', {}, { context: 'egift' })
            }
          </div>
        </ConditionalView>
        <form
          className="egift-validate-form egifts-form-wrapper"
          method="post"
          id="egift-val-form"
          onSubmit={(e) => this.handleSubmit(e)}
        >
          <div className="egift-input-textfield-item egift-verify-card-textfield">
            <input
              type="text"
              name="egift-card-number"
              className="egift-card-number"
              readOnly={enableVerifyCode}
              onBlur={(e) => this.handleEvent(e)}
              onInput={(e) => allowWholeNumbers(e)}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('eGift Card Number', {}, { context: 'egift' })}</label>
            <div id="egift-card-number-error" className="error" />
          </div>
          <ConditionalView condition={enableVerifyCode === false}>
            <div className="action-buttons">
              <button
                className="egift-button"
                id="egift-redeem-get-code-button"
                type="submit"
                onClick={() => { this.state.action = 'getCode'; }}
              >
                {Drupal.t('Get Code', {}, { context: 'egift' })}
              </button>
            </div>
          </ConditionalView>
          <ConditionalView condition={enableVerifyCode}>
            <div className="egift-verify-code egift-input-textfield-item ">
              <input
                type="text"
                name="otp-code"
                className="egift-card-verify-code"
                onFocus={() => this.clearErrors()}
                onBlur={(e) => this.handleEvent(e)}
              />
              <div className="c-input__bar" />
              <label>{Drupal.t('Enter verification code', {}, { context: 'egift' })}</label>
              <div id="egift-code-error" className="error" />
            </div>
            <div className="egift-linked-card-links">
              <div className="egift-resend-wrapper">
                <div className="egift-resend-wrapper__left">
                  <span className="egift-light-text">{Drupal.t('Didn\'t receive?', {}, { context: 'egift' })}</span>
                  <span className="egift-resend-code-text" onClick={(e) => this.handleResendCode(e)}>
                    {Drupal.t('Resend Code', {}, { context: 'egift' })}
                  </span>
                </div>
                <div className="egift-resend-wrapper__right">
                  <span className="egift-change-card-text" onClick={(e) => this.handleChangeCardNumber(e)}>
                    {Drupal.t('Change Card?', {}, { context: 'egift' })}
                  </span>
                </div>
              </div>
            </div>
            <div className="action-buttons">
              <button
                className="egift-button"
                id="egift-redeem-button"
                type="submit"
                onClick={() => { this.state.action = 'verifyOtp'; }}
              >
                {Drupal.t('Verify', {}, { context: 'egift' })}
              </button>
            </div>
          </ConditionalView>

        </form>
      </div>
    );
  }
}

export default EgiftCardNotLinked;
