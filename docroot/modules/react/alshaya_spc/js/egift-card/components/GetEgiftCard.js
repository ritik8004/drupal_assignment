import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../js/utilities/strings';
import { egiftCardHeader, egiftFormElement, isEgiftUnsupportedPaymentMethod } from '../../utilities/egift_util';

// Validation function.
const handleEgiftDetailValidation = (e) => {
  let errors = false;
  const { egift_card_number: egiftCardNumber, egift_email: egiftEmail } = e.target.elements;
  // Email validation.
  if (egiftEmail.value.length === 0) {
    document.getElementById('egift_email_error').innerHTML = getStringMessage('form_error_email');
    errors = true;
  } else {
    document.getElementById('egift_email_error').innerHTML = '';
  }
  // Egift card number validation.
  if (egiftCardNumber.value.length === 0) {
    document.getElementById('egift_card_number_error').innerHTML = getStringMessage('form_egift_card_number');
    errors = true;
  } else if (!egiftCardNumber.value.match(/^[a-z0-9A-Z]+$/i)) {
    // Check if the card number is valid or not.
    document.getElementById('egift_card_number_error').innerHTML = getStringMessage('egift_valid_card_number');
    errors = true;
  } else {
    document.getElementById('egift_card_number_error').innerHTML = '';
  }

  return !errors;
};

// Handles form submission.
const handleSubmit = (e, props) => {
  e.preventDefault();
  // Return if paymethod method is disabled.
  const { paymentMethod } = props;
  if (hasValue(paymentMethod) && isEgiftUnsupportedPaymentMethod(paymentMethod)) {
    return;
  }
  // Perform validation.
  const valid = handleEgiftDetailValidation(e);
  const { getCode } = props;
  // Proceed only if validation is passed.
  if (valid) {
    const { egift_card_number: egiftCardNumber, egift_email: egiftEmail } = e.target.elements;
    const status = getCode(egiftCardNumber.value, egiftEmail.value);
    // Display inline error message if OTP is not sent.
    if (!status) {
      document.getElementById('egift_getcard_error').innerHTML = Drupal.t('Error while sending OTP, Please try again.', {}, { context: 'egift' });
    }
  }
};

// Provies the egift card form.
const GetEgiftCard = (props) => {
  const { egiftEmail, egiftCardNumber, paymentMethod } = props;
  // Check if the payment method is supported or not.
  let redemptionDisabled = false;
  let additionalClasses = '';
  if (hasValue(paymentMethod)) {
    // Set redemption status based on the payment method.
    redemptionDisabled = isEgiftUnsupportedPaymentMethod(paymentMethod);

    // Add `in-active` class if redemptionDisabled property is true.
    additionalClasses = redemptionDisabled
      ? `${additionalClasses} in-active`
      : `${additionalClasses} active`;
  }

  return (
    <div className={`egift-wrapper ${additionalClasses}`}>
      <div id="egift_getcard_error" className="error" />
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
            value: egiftCardNumber,
            disabled: redemptionDisabled,
          })}
          {egiftFormElement({
            type: 'email',
            name: 'email',
            placeholder: 'Email address',
            className: 'email',
            value: egiftEmail,
            disabled: redemptionDisabled,
          })}
          {egiftFormElement({
            type: 'submit',
            name: 'button',
            buttonText: 'Get Code',
            disabled: redemptionDisabled,
          })}
        </form>
      </div>
    </div>
  );
};

export default GetEgiftCard;
