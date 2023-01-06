import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../js/utilities/strings';
import {
  egiftCardHeader,
  egiftFormElement,
  isEgiftRedemptionDone,
  isEgiftUnsupportedPaymentMethod,
  selfCardTopup,
} from '../../utilities/egift_util';

// Validation function.
const handleEgiftDetailValidation = (e, props) => {
  let errors = false;
  const { egift_card_number: cardNumber } = e.target.elements;
  const egiftCardNumber = cardNumber.value.trim();
  const { cart: cartData, redemptionDisabled } = props;
  // Don't do anything if redemption is disabled.
  if (redemptionDisabled) {
    return errors;
  }
  // Egift card number validation.
  if (egiftCardNumber.length === 0) {
    document.getElementById('egift_card_number_error').innerHTML = getStringMessage('form_egift_card_number');
    errors = true;
  } else if (!egiftCardNumber.match(/^[a-z0-9A-Z]+$/i)) {
    // Check if the card number is valid or not.
    document.getElementById('egift_card_number_error').innerHTML = getStringMessage('egift_valid_card_number');
    errors = true;
  } else if (selfCardTopup(cartData, egiftCardNumber)) {
    document.getElementById('egift_card_number_error').innerHTML = Drupal.t('You cannot redeem the same card which you\'re trying to topup.', {}, { context: 'egift' });
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
  const valid = handleEgiftDetailValidation(e, props);
  // Proceed only if validation is passed.
  if (valid) {
    const { getCode } = props;
    const { egift_card_number: egiftCardNumber } = e.target.elements;
    const result = await getCode(egiftCardNumber.value.trim());
    // Display inline error message if OTP is not sent.
    if (result.error) {
      document.getElementById('egift_card_number_error').innerHTML = result.message;
      // Push error message to GTM.
      Drupal.logJavascriptError('egiftcard-number-verification', result.gtmMessage, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }
  }
};

// Provies the egift card form.
const GetEgiftCard = (props) => {
  const { egiftCardNumber, redemptionDisabled, cart } = props;
  // Check if the payment method is supported or not.
  let additionalClasses = '';
  if (hasValue(redemptionDisabled)) {
    // Add `in-active` class if redemptionDisabled property is true.
    additionalClasses = redemptionDisabled
      ? `${additionalClasses} in-active`
      : `${additionalClasses} active`;
  }
  // Disable redemption if non supported payment method selected or
  // linked card redemption checkbox is selected.
  const disable = (redemptionDisabled || isEgiftRedemptionDone(cart, 'linked'));


  return (
    <div className={`egift-wrapper ${additionalClasses}`}>
      {egiftCardHeader({
        egiftHeading: Drupal.t('Verify eGift Card to redeem from card balance', {}, { context: 'egift' }),
        egiftSubHeading: Drupal.t('We’ll send a verification code to your email to verify eGift card', {}, { context: 'egift' }),
      })}
      <div className="egift-form-wrapper">
        <form
          className="egift-redeem-get-code-form"
          method="post"
          id="egift-redeem-get-code-form"
          onSubmit={(e) => handleSubmit(e, props)}
        >
          {egiftFormElement({
            type: 'text',
            name: 'card_number',
            label: Drupal.t('eGift Card Number', {}, { context: 'egift' }),
            className: 'card-number',
            value: egiftCardNumber,
            disabled: disable,
          })}
          <div className="egift-get-code-redeem-submit-btn">
            {egiftFormElement({
              type: 'submit',
              name: 'button',
              buttonText: 'Get Code',
              disabled: disable,
            })}
          </div>
        </form>
      </div>
    </div>
  );
};

export default GetEgiftCard;
