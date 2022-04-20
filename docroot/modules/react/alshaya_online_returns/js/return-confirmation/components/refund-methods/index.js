import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const RefundMethods = ({
  paymentInfo,
}) => {
  if (!hasValue(paymentInfo)) {
    return null;
  }
  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        <div className="method-list-wrapper">
          {Object.keys(paymentInfo).map((method) => (
            <div key={method} className="method-wrapper">
              <div className="card-detail">
                <span className="payment-type">
                  { Drupal.t('@card_type card ending in @card_number', { '@card_type': paymentInfo[method].card_type, '@card_number': paymentInfo[method].card_number }) }
                </span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </>
  );
};

export default RefundMethods;
