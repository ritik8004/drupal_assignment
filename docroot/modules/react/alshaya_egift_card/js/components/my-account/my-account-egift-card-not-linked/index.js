import React from 'react';
import ConditionalView
  from '../../../../../js/utilities/components/conditional-view';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import logger from '../../../../../js/utilities/logger';

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
        // Check for error from handleResponse.
        if (typeof response.data !== 'undefined' && typeof response.data.error !== 'undefined' && response.data.error) {
          document.getElementById('egift-card-number-error').innerHTML = response.data.error_message;
          logger.error('Error while unlinking card. @error', { '@error': JSON.stringify(response.data) });
        }

        // If response is true, otp is send, show verify otp fields.
        if (typeof response.data !== 'undefined' && response.data === true) {
          this.setState({
            enableVerifyCode: true,
          });
        }
      });
  }

  // handle submit.
  handleSubmit = (e) => {
    e.preventDefault();
    const { action } = this.state;
    switch (action) {
      case 'verifyOtp':
        // @todo verify otp.
        break;
      default:
        this.getOtpCode();
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
            Drupal.t('We’ll send a verification code to your email to verify and link eGift card', {}, { context: 'egift' })
          }
        </div>
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
              placeholder="eGift Card Number"
              className="egift-card-number"
              required
              disabled={enableVerifyCode}
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
                name="verificationCode"
                placeholder={Drupal.t('Enter verification code', {}, { context: 'egift' })}
                className="egift-card-verify-code"
                required
              />
              <div id="egift-code-error" className="error" />
            </div>
            <button
              className="egift-button"
              id="egift-redeem-button"
              type="submit"
              onClick={() => { this.state.action = 'verifyOtp'; }}
            >
              {Drupal.t('Verify')}
            </button>
          </ConditionalView>

        </form>
      </div>
    );
  }
}

export default EgiftCardNotLinked;
