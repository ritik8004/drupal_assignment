import React from 'react';
import getStringMessage from '../../../../js/utilities/strings';
import { egiftCardHeader, egiftFormElement } from '../../utilities/egift_util';

export default class ValidateEgiftCard extends React.Component {
  // handle submit.
  handleSubmit = (e) => {
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
      const status = codeValidation(egiftCode.value);
      if (!status) {
        document.getElementById('egift_verification_code_error').innerHTML = Drupal.t('Something went wrong, please try again later.', {}, { context: 'egift' });
      }
      return status;
    }

    return false;
  }

  // Resend the code for egift card verification.
  handleResendCode = () => {
    const { resendCode, egiftEmail, egiftCardNumber } = this.props;
    const status = resendCode(egiftCardNumber, egiftEmail);
    // If status if false then display and inline error.
    if (!status) {
      document.getElementById('egift_verification_code_error').innerHTML = Drupal.t('Error while sending OTP, Please try again.', {}, { context: 'egift' });

      return false;
    }

    return true;
  }

  // Move back to the getEgift component.
  handleChangeCard = () => {
    const { changeEgiftCard } = this.props;
    changeEgiftCard();
  }

  render = () => {
    const { egiftEmail } = this.props;
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
