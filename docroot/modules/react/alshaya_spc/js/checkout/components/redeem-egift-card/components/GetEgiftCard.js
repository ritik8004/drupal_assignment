import React from 'react';
import getStringMessage from '../../../../utilities/strings';
import { egiftCardHeader, egiftFormElement } from '../../../../utilities/egift_util';

// Validation function.
const handleEgiftDetailValidation = (e) => {
  let errors = false;
  const { egift_card_number: egiftCardNumber, egift_email: egiftEmailId } = e.target.elements;
  // Email validation.
  if (egiftEmailId.value.length === 0) {
    document.getElementById('egift_email_error').innerHTML = getStringMessage('form_error_email');
    errors = true;
  } else {
    document.getElementById('egift_email_error').innerHTML = '';
  }
  // Egift card number validation.
  if (egiftCardNumber.value.length === 0) {
    document.getElementById('egift_card_number_error').innerHTML = getStringMessage('form_egift_card_number');
    errors = true;
  } else {
    document.getElementById('egift_card_number_error').innerHTML = '';
  }
  // @todo Special card validation is required.

  if (errors) {
    return false;
  }

  return true;
};

// Handles form submission.
const handleSubmit = (e, props) => {
  e.preventDefault();
  // Perform validation.
  const valid = handleEgiftDetailValidation(e);
  if (valid) {
    const { egift_card_number: egiftCardNumber, egift_email: egiftEmailId } = e.target.elements;
    props.getCode(egiftCardNumber.value, egiftEmailId.value);
  } else {
    return false;
  }
  return true;
};

// Provies the egift card form.
const GetEgiftCard = (props) => (
  <div className="egift-wrapper">
    {egiftCardHeader({
      egiftHeading: Drupal.t('Verify eGift Card to redeem from card balance', {}, { context: 'egift' }),
      egiftSubHeading: Drupal.t('We’ll send a verification code to your email to verify eGift card', {}, { context: 'egift' }),
    })}
    <div className="egift-form-wrapper">
      <form
        className="egift-get-form"
        method="post"
        id="egift-get-form"
        onSubmit={(e) => handleSubmit(e, props)}
      >
        {egiftFormElement({
          type: 'text',
          name: 'card_number',
          placeholder: 'eGift Card Number',
          className: 'card-number',
        })}
        {egiftFormElement({
          type: 'email',
          name: 'email',
          placeholder: 'Email address',
          className: 'email',
        })}
        {egiftFormElement({
          type: 'submit',
          name: 'button',
          buttonText: 'Get Code',
        })}
      </form>
    </div>
  </div>
);

export default GetEgiftCard;
