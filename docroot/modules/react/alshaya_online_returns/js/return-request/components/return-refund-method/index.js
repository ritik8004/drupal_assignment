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
          <div className="method-listing-label">
            { Drupal.t('Your refund will be credited back to the following payment methods.', {}, { context: 'online_returns' }) }
          </div>
          <div className="method-list-wrapper">
            {Object.keys(paymentDetails).map((method) => (
              <div key={method} className="method-wrapper">
                <div className="card-icon">
                  <CardTypeSVG type={paymentDetails[method].payment_type.toLowerCase()} class={`${paymentDetails[method].payment_type.toLowerCase()} is-active`} />
                </div>
                <div className="card-detail">
                  <span className="payment-type bold-text">
                    { Drupal.t('@card_type Card', { '@card_type': paymentDetails[method].card_type }, {}, { context: 'online_returns' }) }
                  </span>
                  <span>
                    {' '}
                    { Drupal.t('ending in', {}, { context: 'online_returns' }) }
                    {' '}
                  </span>
                  <span className="payment-info bold-text">
                    { Drupal.t('@card_number', { '@card_number': paymentDetails[method].card_number }, {}, { context: 'online_returns' }) }
                  </span>
                </div>
              </div>
            ))}
          </div>
          <div className="refund-message">
            { Drupal.t('Estimated refund in 3-5 business days after we receive the item', {}, { context: 'online_returns' }) }
          </div>
        </div>
        <div className="refund-message">
          { Drupal.t('Estimated refund in 3-5 business days after we receive the item.', {}, { context: 'online_returns' }) }
        </div>
      </div>
    </>
  );
};


export default ReturnRefundMethod;
