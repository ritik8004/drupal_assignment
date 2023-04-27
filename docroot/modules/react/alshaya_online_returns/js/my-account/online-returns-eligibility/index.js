import React from 'react';
import {
  isReturnEligible,
  getReturnExpiration,
  getOrderType,
  getPaymentMethod,
  isReturnWindowClosed,
  isBigTicketOrder,
} from '../../utilities/online_returns_util';
import ReturnEligibilityMessage from '../../common/return-eligibility-message';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getReturnsByOrderId } from '../../utilities/return_api_helper';

class OnlineReturnsEligibility extends React.Component {
  constructor(props) {
    super(props);
    const { orderId } = this.props;

    this.state = {
      orderId,
      returnData: this.getReturnData(),
    };
  }

  /**
   * Function to get the data related to returns.
   */
  getReturnData = () => {
    const { orderDetails, selector } = this.props;
    const { data } = orderDetails.detail;
    const {
      orderEntityId,
      isReturnEligible: returnEligible,
    } = drupalSettings.onlineReturns.recentOrders[data.orderId];
    // Return from here if order is not eligible for return.
    if (!returnEligible) {
      // Add the `content-loaded` class to remove the skeletal.
      selector.parentNode.classList.add('content-loaded');
      return;
    }

    const returns = getReturnsByOrderId(orderEntityId);

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

          this.setState({
            orderId: data.orderId,
            returnData: allReturns,
          });
          // Add the `content-loaded` class to remove the skeletal.
          selector.parentNode.classList.add('content-loaded');

          // Add the `return-window-closed` class based on the validation.
          if (isReturnWindowClosed(getReturnExpiration(data.orderId))) {
            selector.classList.add('return-window-closed');
          }
        }
      });
    }
  }

  render() {
    const { orderId, returnData } = this.state;
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
        isBigTicketOrder={isBigTicketOrder(orderId)}
      />
    );
  }
}

export default OnlineReturnsEligibility;
