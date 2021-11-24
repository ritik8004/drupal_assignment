import React from 'react';
import getStringMessage from '../../utilities/strings';
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
      codeValidation(egiftCode.value);
    }
  }

  // Resend the code for egift card verification.
  handleResendCode = () => {

  }

  // Move back to the getEgift component.
  handleChangeCard = () => {

  }

  render = () => {
    const { emailId } = this.props;
    return (
      <>
        <div className="egift-wrapper">
          {egiftCardHeader({
            egiftHeading: Drupal.t('Verify eGift Card to redeem from card balance', {}, { context: 'egift' }),
            egiftSubHeading: Drupal.t('Verification code sent to @email', { '@email': emailId }, { context: 'egift' }),
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
                  {Drupal.t('Dint receive?', {}, { context: 'egift' })}
                  <strong onClick={this.handleResendCode}>Resend Code</strong>
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
