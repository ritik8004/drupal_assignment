import React from 'react';
import ReturnEligibility from '../../../../../alshaya_online_returns/js/order-details/return-eligibility';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const OnlineReturnEligibility = (props) => {
  const { order } = props;
  if (!hasValue(order.online_returns_status)) {
    return null;
  }

  return (
    <div className="order-item-row online-returns-eligibility-message">
      <div>
        <div id="online-returns-eligibility-window">
          <ReturnEligibility />
        </div>
      </div>
    </div>
  );
};

export default OnlineReturnEligibility;
