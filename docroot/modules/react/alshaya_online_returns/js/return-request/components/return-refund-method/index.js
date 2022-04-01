import React from 'react';
import CardTypeSVG from '../../../../../alshaya_spc/js/svg-component/card-type-svg';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const ReturnRefundMethod = ({
  paymentDetails,
}) => {
  if (!hasValue(paymentDetails)) {
    return null;
  }
  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        <div className="refund-method-listing">
          <span className="method-listing-label">
            { Drupal.t('Your refund will be credited back to the following payment methods.', {}, { context: 'online_returns' }) }
          </span>
          <div className="method-list-wrapper">
            {Object.keys(paymentDetails).map((method) => (
              <div key={method} className="method-wrapper">
                <div className="card-icon">
                  <CardTypeSVG type={paymentDetails[method].payment_type.toLowerCase()} class={`${paymentDetails[method].payment_type.toLowerCase()} is-active`} />
                </div>
                <div className="card-detail">
                  { Drupal.t('@card_type Card ending in @card_number', { '@card_type': paymentDetails[method].card_type, '@card_number': paymentDetails[method].card_number }, {}, { context: 'online_returns' }) }
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </>
  );
};


export default ReturnRefundMethod;
