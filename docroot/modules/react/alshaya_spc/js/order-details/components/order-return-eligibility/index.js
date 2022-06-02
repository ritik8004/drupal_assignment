import React from 'react';
import ReturnEligibility from '../../../../../alshaya_online_returns/js/order-details/return-eligibility';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isOnlineReturnsEnabled } from '../../../../../js/utilities/onlineReturnsHelper';

const OrderReturnEligibility = (props) => {
  const { order, returns } = props;
  if (!isOnlineReturnsEnabled() || !hasValue(order.online_returns_status)) {
    return null;
  }

  const {
    onlineReturns: {
      products,
    },
  } = drupalSettings;

  // Return if products are empty.
  if (!hasValue(products)) {
    return null;
  }

  return (
    <div className="order-item-row online-returns-eligibility-message">
      <div>
        <div id="online-returns-eligibility-window">
          <ReturnEligibility returns={returns} />
        </div>
      </div>
    </div>
  );
};

export default OrderReturnEligibility;
