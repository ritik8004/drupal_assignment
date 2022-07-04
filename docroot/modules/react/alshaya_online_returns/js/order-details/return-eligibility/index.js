import React from 'react';
import ReturnEligibilityMessage from '../../common/return-eligibility-message';

const ReturnEligibility = (returns) => {
  const {
    onlineReturns: {
      orderId,
      isReturnEligible,
      returnExpiration,
      paymentMethod,
      orderType,
    },
  } = drupalSettings;

  return (
    <ReturnEligibilityMessage
      orderId={orderId}
      isReturnEligible={isReturnEligible}
      returnExpiration={returnExpiration}
      paymentMethod={paymentMethod}
      orderType={orderType}
      returns={returns}
    />
  );
};

export default ReturnEligibility;
