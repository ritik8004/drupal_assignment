import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../js/utilities/strings';
import { egiftCardHeader, egiftFormElement, isEgiftUnsupportedPaymentMethod } from '../../utilities/egift_util';

// Validation function.
const handleEgiftDetailValidation = (e) => {
  let errors = false;
  const { egift_card_number: egiftCardNumber } = e.target.elements;
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
const handleSubmit = async (e, props) => {
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
    const { egift_card_number: egiftCardNumber } = e.target.elements;
    const errors = await getCode(egiftCardNumber.value);
    // Display inline error message if OTP is not sent.
    if (errors) {
      document.getElementById('egift_card_number_error').innerHTML = Drupal.t('Error while sending OTP, Please try again.', {}, { context: 'egift' });
    }
  }
};

// Provies the egift card form.
const GetEgiftCard = (props) => {
  const { egiftCardNumber, redemptionDisabled } = props;
  // Check if the payment method is supported or not.
  let additionalClasses = '';
  if (hasValue(redemptionDisabled)) {
    // Add `in-active` class if redemptionDisabled property is true.
    additionalClasses = redemptionDisabled
      ? `${additionalClasses} in-active`
      : `${additionalClasses} active`;
  }

  return (
    <div className={`egift-wrapper ${additionalClasses}`}>
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
