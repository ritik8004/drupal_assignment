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
      isBigTicketOrder,
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
      isBigTicketOrder={isBigTicketOrder}
    />
  );
};

export default ReturnEligibility;
