import React from 'react';
import parse from 'html-react-parser';
import getStringMessage from '../../../utilities/strings';

const PaymentMethodCheckoutComUpapiFawry = (props) => {
  const countryMobileCodeMaxLength = drupalSettings.mobile_maxlength;

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
        <p>
          {getStringMessage('fawry_payment_option_prefix_description')}
        </p>
      </div>
      <div className="spc-type-textfield">
        <div className="field-wrapper">
          <input
            type="email"
            name="fawry-email"
            readOnly="readOnly"
            required
            defaultValue={email}
          />
        </div>
        <div className="c-input__bar" />
        <label>{Drupal.t('Email')}</label>
        <div id="fawry-email-error" />
      </div>
      <div className="spc-type-textfield">
        <label>{Drupal.t('Mobile Number')}</label>
        <div className="field-wrapper">
          <input
            maxLength={countryMobileCodeMaxLength}
            type="tel"
            name="fawry-mobile-number"
            readOnly="readOnly"
            required
            defaultValue={telephone}
          />
        </div>
        <div className="c-input__bar" />
        <div id="fawry-mobile-number-error" />
      </div>
      <div className="fawry-suffix-description">
        <p>
          {
            parse(getStringMessage('fawry_payment_option_suffix_description'))
          }
        </p>
      </div>
    </div>
  );
};

export default PaymentMethodCheckoutComUpapiFawry;
