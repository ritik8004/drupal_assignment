import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import ReturnEligibility from '../../../../../alshaya_online_returns/js/order-details/return-eligibility';

const OnlineReturns = (props) => {
  const { order } = props;

  return (
    <>
      <ConditionalView condition={order.online_returns_status}>
        <div className="order-item-row online-returns-eligibility-message">
          <div>
            <div id="online-returns-eligibility-window">
              <ReturnEligibility />
            </div>
          </div>
        </div>
      </ConditionalView>
    </>
  );
};

export default OnlineReturns;
