import React from 'react';
import getStringMessage from '../../../../js/utilities/strings';
import { egiftCardHeader, egiftFormElement } from '../../utilities/egift_util';

export default class ValidateEgiftCard extends React.Component {
  // handle submit.
  handleSubmit = async (e) => {
    e.preventDefault();
    // Extract the code .
    const { egift_verification_code: egiftCode } = e.target.elements;
    const { codeValidation } = this.props;
    let errors = false;
    if (egiftCode.value.length === 0) {
      document.getElementById('egift_verification_code_error').innerHTML = getStringMessage('form_egift_code');
      errors = true;
    } else {
      document.getElementById('egift_verification_code_error').innerHTML = '';
    }
    if (!errors) {
      const response = await codeValidation(egiftCode.value);
      if (response.error) {
        document.getElementById('egift_verification_code_error').innerHTML = response.message;
        // Push error message to GTM.
        Drupal.logJavascriptError('egiftcard-code-verification', response.gtmMessage, GTM_CONSTANTS.CHECKOUT_ERRORS);
      }
    }
  }

  // Resend the code for e-Gift card verification.
  handleResendCode = async () => {
    // Remove any existing error message and code.
    document.getElementById('egift_verification_code_error').innerHTML = '';
    document.getElementById('egift_verification_code').value = '';

    const { resendCode, egiftCardNumber } = this.props;
    const result = await resendCode(egiftCardNumber);
    // If errors if true then display inline error message.
    if (result.error) {
      document.getElementById('egift_verification_code_error').innerHTML = result.message;
      // Push error message to GTM.
      Drupal.logJavascriptError('egiftcard-code-verification', result.gtmMessage, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }
  }

  // Move back to the getEgift component.
  handleChangeCard = () => {
    const { changeEgiftCard } = this.props;
    changeEgiftCard();
  }

  render = () => {
    const { egiftCardNumber } = this.props;
    return (
      <>
        <div className="egift-wrapper">
          {egiftCardHeader({
            egiftHeading: Drupal.t('Verify eGift Card to redeem from card balance', {}, { context: 'egift' }),
          })}

          <div className="egift-form-wrapper">
            <form
              className="egift-validate-form"
              method="post"
              id="egift-val-form"
              onSubmit={this.handleSubmit}
            >
              <div className="egift-validate-form-input-wrapper">
                {egiftFormElement({
                  type: 'text',
                  name: 'card_number',
                  label: Drupal.t('eGift Card Number', {}, { context: 'egift' }),
                  className: 'card-number',
                  value: egiftCardNumber,
                  disabled: true,
                })}
                {egiftFormElement({
                  type: 'text',
                  name: 'verification_code',
                  label: Drupal.t('Enter verification code', {}, { context: 'egift' }),
                  className: 'verification-code',
                })}
                <div className="egift-redeem-card-links">
                  <div className="egift-resend-wrapper">
                    <div className="egift-resend-wrapper__left">
                      <span className="egift-light-text">{Drupal.t('Didn\'t receive?', {}, { context: 'egift' })}</span>
                      <span className="egift-resend-code-text" onClick={(e) => this.handleResendCode(e)}>
                        {Drupal.t('Resend Code', {}, { context: 'egift' })}
                      </span>
                    </div>
                    <div className="egift-resend-wrapper__right">
                      <span className="egift-change-card-text" onClick={(e) => this.handleChangeCard(e)}>
                        {Drupal.t('Change Card?', {}, { context: 'egift' })}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div className="egift-verify-code-redeem-submit-btn">
                {egiftFormElement({
                  type: 'submit',
                  name: 'button',
                  buttonText: 'Verify',
                })}
              </div>
            </form>
          </div>
        </div>
      </>
    );
  }
}
