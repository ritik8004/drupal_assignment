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
      }
    }
  }

  // Resend the code for egift card verification.
  handleResendCode = async () => {
    const { resendCode, egiftCardNumber } = this.props;
    const result = await resendCode(egiftCardNumber);
    // If errors if true then display inline error message.
    if (result.error) {
      document.getElementById('egift_verification_code_error').innerHTML = result.message;
    }
  }

  // Move back to the getEgift component.
  handleChangeCard = () => {
    const { changeEgiftCard } = this.props;
    changeEgiftCard();
  }

  render = () => {
    const { egiftEmail, egiftCardNumber } = this.props;
    return (
      <>
        <div className="egift-wrapper">
          {egiftCardHeader({
            egiftHeading: Drupal.t('Verify eGift Card to redeem from card balance', {}, { context: 'egift' }),
            egiftSubHeading: Drupal.t('Verification code sent to @email', { '@email': egiftEmail }, { context: 'egift' }),
          })}

          <div className="egift-form-wrapper">
            <form
              className="egift-validate-form"
              method="post"
              id="egift-val-form"
              onSubmit={this.handleSubmit}
            >
              {egiftFormElement({
                type: 'text',
                name: 'card_number',
                placeholder: 'eGift Card Number',
                className: 'card-number',
                value: egiftCardNumber,
                disabled: true,
              })}
              {egiftFormElement({
                type: 'text',
                name: 'verification_code',
                placeholder: 'Enter verification code',
                className: 'verification-code',
              })}
              {egiftFormElement({
                type: 'submit',
                name: 'button',
                buttonText: 'Verify',
              })}

              <div className="modify-wrapper">
                <span>
                  {Drupal.t('Didn’t receive?', {}, { context: 'egift' })}
                  <strong onClick={this.handleResendCode}>{Drupal.t('Resend Code', {}, { context: 'egift' })}</strong>
                </span>
                <span onClick={this.handleChangeCard}>{Drupal.t('Change Card?', {}, { context: 'egift' })}</span>
              </div>
            </form>
          </div>
        </div>
      </>
    );
  }
}
