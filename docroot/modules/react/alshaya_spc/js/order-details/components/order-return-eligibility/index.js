import React from 'react';
import ReturnEligibility from '../../../../../alshaya_online_returns/js/order-details/return-eligibility';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import isOnlineReturnsEnabled from '../../../../../js/utilities/onlineReturnsHelper';

const OrderReturnEligibility = (props) => {
  const { order } = props;
  if (!isOnlineReturnsEnabled() || !hasValue(order.online_returns_status)) {
    return null;
  }

  const {
    onlineReturns: {
      products,
    },
  } = drupalSettings;

  return (
    <ConditionalView condition={hasValue(products)}>
      <div className="order-item-row online-returns-eligibility-message">
        <div>
          <div id="online-returns-eligibility-window">
            <ReturnEligibility />
          </div>
        </div>
      </div>
    </ConditionalView>
  );
};

export default OrderReturnEligibility;
