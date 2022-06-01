import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../../../return-confirmation/components/card-details';
import { sortPaymentByWeight } from '../../../utilities/return_request_util';

const ReturnRefundMethod = ({
  paymentDetails,
}) => {
  if (!hasValue(paymentDetails)) {
    return null;
  }
  // Get sorted payment details by weight.
  const sortedPaymentData = sortPaymentByWeight(paymentDetails);
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
          <CardDetails paymentDetails={sortedPaymentData} />
          <div className="refund-message">
            { Drupal.t('Estimated refund in 3-5 business days after we receive the item', {}, { context: 'online_returns' }) }
          </div>
        </div>
      </div>
    </>
  );
};

export default ReturnRefundMethod;
