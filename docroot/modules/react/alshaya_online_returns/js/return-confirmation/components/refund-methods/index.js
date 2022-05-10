import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../card-details';

const RefundMethods = ({
  paymentInfo,
}) => {
  if (!hasValue(paymentInfo)) {
    return null;
  }
  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title light">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        <CardDetails paymentDetails={paymentInfo} />
      </div>
    </>
  );
};

export default RefundMethods;
