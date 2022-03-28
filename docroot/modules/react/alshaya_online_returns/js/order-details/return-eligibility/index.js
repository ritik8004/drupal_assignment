import React from 'react';
import ReturnEligibilityMessage from '../../common/return-eligibility-message';

const ReturnEligibility = () => {
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
    />
  );
};

export default ReturnEligibility;
