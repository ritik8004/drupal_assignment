import React from 'react';
import ConditionalView
  from '../../../../../js/utilities/components/conditional-view';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import logger from '../../../../../js/utilities/logger';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';

class EgiftCardNotLinked extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      enableVerifyCode: false,
      action: '',
    };
  }

  /**
   * Get opt code for card number verification.
   */
  getOtpCode = () => {
    // Get logged in user email address.
    const { userEmailID } = drupalSettings.userDetails;
    return callMagentoApi(`/V1/sendemailotp/email/${userEmailID}`, 'GET', {})
      .then((response) => {
        removeFullScreenLoader();
        // Check for error from handleResponse.
        if (typeof response.data !== 'undefined' && typeof response.data.error !== 'undefined' && response.data.error) {
          document.getElementById('egift-card-number-error').innerHTML = response.data.error_message;
          logger.error('Error while unlinking card. @error', { '@error': JSON.stringify(response.data) });
          return false;
        }

        // If response is true, otp is send, show verify otp fields.
        if (typeof response.data !== 'undefined' && response.data === true) {
          this.setState({
            enableVerifyCode: true,
          });
          document.getElementById('resend-success').innerHTML = Drupal.t('Verification code is send to email address registered with the card number.', {
            '@email': drupalSettings.userDetails.userEmailID,
          }, { context: 'egift' });
        }
        return true;
      });
  }

  /**
   * Handle resend otp code action.
   */
  handleResendCode = (e) => {
    e.preventDefault();
    showFullScreenLoader();
    this.getOtpCode().then((response) => {
      if (typeof response !== 'undefined' && response) {
        document.getElementById('resend-success').innerHTML = Drupal.t('Verification code is send to email address registered with the card number.', {
          '@email': drupalSettings.userDetails.userEmailID,
        }, { context: 'egift' });
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
    const cardNumber = data.get('egift-card-number');

    // Validate Card number.
    if (cardNumber === '') {
      // If card number is empty show error.
      document.getElementById('egift-card-number-error').innerHTML = Drupal.t('Please enter an eGift card number.', {}, { context: 'egift' });
      return;
    }

    // If card number is non english show error.
    if (/[\u0600-\u06FF]/.test(cardNumber)) {
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
        if (/[\u0600-\u06FF]/.test(otp)) {
          document.getElementById('egift-code-error').innerHTML = Drupal.t('Please enter a valid verification code.', {}, { context: 'egift' });
          return;
        }

        showFullScreenLoader();

        // Verify otp.
        const { userEmailID } = drupalSettings.userDetails;
        callMagentoApi(`/V1/verifyemailotp/email/${userEmailID}/otp/${otp}`, 'GET').then((response) => {
          // Check for error from handleResponse.
          if (typeof response.data !== 'undefined' && typeof response.data.error !== 'undefined' && response.data.error) {
            document.getElementById('egift-code-error').innerHTML = response.data.error_message;
            logger.error('Error while verifying otp. @error', { '@error': JSON.stringify(response.data) });
            removeFullScreenLoader();
            return false;
          }

          // If response is true, otp is verified, link card to the customer.
          if (typeof response.data !== 'undefined' && response.data === true) {
            // Get params for link card api.
            const params = {
              card_number: cardNumber,
              customerId: drupalSettings.userDetails.customerId,
            };

            // Call link eGift card API.
            callMagentoApi('/V1/egiftcard/link', 'POST', params).then((result) => {
              removeFullScreenLoader();
              // Check for error from handleResponse.
              if (typeof result.data !== 'undefined' && typeof result.data.error !== 'undefined' && result.data.error) {
                document.getElementById('egift-code-error').innerHTML = result.data.error_message;
                logger.error('Error while linking card to customer. @error', { '@error': JSON.stringify(result.data) });
              }

              // If response type is true, then show linked card.
              if (typeof result.data !== 'undefined' && result.data.response_type === true) {
                const { showCard } = this.props;
                showCard();
              }
            });
          }
          return true;
        });

        break;
      }
      default: {
        showFullScreenLoader();
        this.getOtpCode();
      }
    }
  }

  render() {
    const { enableVerifyCode } = this.state;

    return (
      <div className="egift-notlinked-warpper">
        <div className="egift-notlinked-title">{Drupal.t('Link my egift card', {}, { context: 'egift' })}</div>
        <div className="egift-link-card-text">
          {
            Drupal.t('You dont have any eGift card linked to your account, link card to use it for your purchases', {}, { context: 'egift' })
          }
        </div>
        <div className="egift-link-card-instruction">
          {
            Drupal.t('We\'ll send a verification code to your email to verify and link eGift card', {}, { context: 'egift' })
          }
        </div>
        <div id="resend-success" className="egift-resend-message" />
        <form
          className="egift-validate-form"
          method="post"
          id="egift-val-form"
          onSubmit={(e) => this.handleSubmit(e)}
        >
          <div className="egift-textfield">
            <input
              type="text"
              name="egift-card-number"
              placeholder={Drupal.t('eGift Card Number', {}, { context: 'egift' })}
              className="egift-card-number"
              readOnly={enableVerifyCode}
              onFocus={() => this.clearErrors()}
            />
            <div id="egift-card-number-error" className="error" />
          </div>
          <ConditionalView condition={enableVerifyCode === false}>
            <button
              className="egift-button"
              id="egift-redeem-get-code-button"
              type="submit"
              onClick={() => { this.state.action = 'getCode'; }}
            >
              {Drupal.t('Get Code', {}, { context: 'egift' })}
            </button>
          </ConditionalView>
          <ConditionalView condition={enableVerifyCode}>
            <div className="egift-verify-code">
              <input
                type="text"
                name="otp-code"
                placeholder={Drupal.t('Enter verification code', {}, { context: 'egift' })}
                className="egift-card-verify-code"
                onFocus={() => this.clearErrors()}
              />
              <div id="egift-code-error" className="error" />
            </div>
            <div className="egift-linked-card-links">
              <div className="egift-resend">
                <span>{Drupal.t('Didn\'t receive?', {}, { context: 'egift' })}</span>
                <span onClick={(e) => this.handleResendCode(e)}>
                  {Drupal.t('Resend Code', {}, { context: 'egift' })}
                </span>
                <span onClick={(e) => this.handleChangeCardNumber(e)}>
                  {Drupal.t('Change Card?', {}, { context: 'egift' })}
                </span>
              </div>
            </div>
            <button
              className="egift-button"
              id="egift-redeem-button"
              type="submit"
              onClick={() => { this.state.action = 'verifyOtp'; }}
            >
              {Drupal.t('Verify', {}, { context: 'egift' })}
            </button>
          </ConditionalView>

        </form>
      </div>
    );
  }
}

export default EgiftCardNotLinked;
