import React from 'react';
import ReturnEligibilityMessage from '../../common/return-eligibility-message';

const ReturnEligibility = () => {
  const {
    onlineReturns: {
      orderId,
      isReturnEligible,
      returnExipiration,
      paymentMethod,
      orderType,
    },
  } = drupalSettings;

  return (
    <ReturnEligibilityMessage
      orderId={orderId}
      isReturnEligible={isReturnEligible}
      returnExipiration={returnExipiration}
      paymentMethod={paymentMethod}
      orderType={orderType}
    />
  );
};

export default ReturnEligibility;
