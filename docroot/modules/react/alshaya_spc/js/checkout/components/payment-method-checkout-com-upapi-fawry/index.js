import React from 'react';
import parse from 'html-react-parser';
import getStringMessage from '../../../utilities/strings';
import TextField from '../../../utilities/textfield';
import { cleanMobileNumber } from '../../../utilities/checkout_util';

const PaymentMethodCheckoutComUpapiFawry = (props) => {
  const {
    cart: {
      cart: {
        billing_address: {
          email,
          telephone,
        },
      },
    },
  } = props;

  return (
    <div className="payment-form-wrapper">
      <div className="fawry-prefix-description">
        {getStringMessage('fawry_payment_option_prefix_description')}
      </div>
      <TextField
        type="email"
        name="fawry-email"
        disabled
        defaultValue={email !== '' ? email : ''}
        className={email !== '' && email !== '' ? 'focus' : ''}
        label={Drupal.t('Email')}
      />
      <TextField
        type="tel"
        name="fawry-mobile-number"
        disabled
        defaultValue={telephone !== '' ? cleanMobileNumber(telephone) : ''}
        className={telephone !== '' && telephone !== '' ? 'focus' : ''}
        label={Drupal.t('Mobile Number')}
      />
      <div className="fawry-suffix-description">
        { parse(getStringMessage('fawry_payment_option_suffix_description')) }
      </div>
    </div>
  );
};

export default PaymentMethodCheckoutComUpapiFawry;
