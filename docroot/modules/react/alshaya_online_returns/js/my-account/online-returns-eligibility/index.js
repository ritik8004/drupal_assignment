import React from 'react';
import ReactDOM from 'react-dom';
import {
  isReturnEligible,
  getReturnExpiration,
  getOrderType,
  getPaymentMethod,
  isReturnWindowClosed,
} from '../../utilities/online_returns_util';
import ReturnEligibilityMessage from '../../common/return-eligibility-message';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getReturnsByOrderId } from '../../utilities/return_api_helper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';

class OnlineReturnsEligibility extends React.Component {
  componentDidMount() {
    document.addEventListener('onRecentOrderView', this.showReturnEligibility, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onRecentOrderView', this.showReturnEligibility, false);
  }

  showReturnEligibility = (orderDetails) => {
    const { data } = orderDetails.detail;
    const { orderEntityId } = drupalSettings.onlineReturns.recentOrders[data.orderId];
    const returns = getReturnsByOrderId(orderEntityId);
    showFullScreenLoader();

    // Unmount component from all orders.
    Object.keys(drupalSettings.onlineReturns.recentOrders).forEach((orderId) => {
      ReactDOM.unmountComponentAtNode(
        document.querySelector(`*[data-order-id="${orderId}"] #online-returns-eligibility-window`),
      );
    });

    if (returns instanceof Promise) {
      returns.then((returnResponse) => {
        if (hasValue(returnResponse) && hasValue(returnResponse.data)) {
          const allReturns = [];
          if (hasValue(returnResponse.data.items)) {
            returnResponse.data.items.forEach((returnItem) => {
              const returnData = {
                returnInfo: returnItem,
              };
              allReturns.push(returnData);
            });
          }
          removeFullScreenLoader();
          // Render component for the selected order.
          const selector = document.querySelector(`*[data-order-id="${data.orderId}"] #online-returns-eligibility-window`);
          if (selector) {
            // Check if return window closed and add return-window-closed class.
            if (isReturnWindowClosed(getReturnExpiration(data.orderId))) {
              document.querySelector(`*[data-order-id="${data.orderId}"] #online-returns-eligibility-window`).classList.add('return-window-closed');
            }

            ReactDOM.render(
              <OnlineReturnsEligibility orderId={data.orderId} returnData={allReturns} />,
              selector,
            );
          }
        }
      });
    }
    return null;
  };

  render() {
    const { orderId, returnData } = this.props;
    // Preparing returns data similar to my orders page components.
    const returns = {
      returns: returnData,
    };
    return (
      <ReturnEligibilityMessage
        orderId={orderId}
        isReturnEligible={isReturnEligible(orderId)}
        returnExpiration={getReturnExpiration(orderId)}
        paymentMethod={getPaymentMethod(orderId)}
        orderType={getOrderType(orderId)}
        returns={returns}
      />
    );
  }
}

export default OnlineReturnsEligibility;
