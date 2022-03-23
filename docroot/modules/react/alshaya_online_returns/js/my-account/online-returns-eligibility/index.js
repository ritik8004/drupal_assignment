import React from 'react';
import ReactDOM from 'react-dom';
import {
  isReturnEligible,
  getReturnExipiration,
  getOrderType,
  getPaymentMethod,
} from '../../utilities/online_returns_util';
import ReturnEligibilityMessage from '../../common/return-eligibility-message';

class OnlineReturnsEligibility extends React.Component {
  componentDidMount() {
    document.addEventListener('onRecentOrderView', this.showReturnEligibility, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onRecentOrderView', this.showReturnEligibility, false);
  }

  showReturnEligibility = (orderDetails) => {
    const { data } = orderDetails.detail;

    // Unmount component from all orders.
    Object.keys(drupalSettings.onlineReturns.recentOrders).forEach((orderId) => {
      ReactDOM.unmountComponentAtNode(
        document.querySelector(`*[data-order-id="${orderId}"] #online-returns-eligibility-window`),
      );
    });

    // Render component for the selected order.
    const selector = document.querySelector(`*[data-order-id="${data.orderId}"] #online-returns-eligibility-window`);
    if (selector) {
      ReactDOM.render(
        <OnlineReturnsEligibility orderId={data.orderId} />,
        selector,
      );
    }

    return null;
  };

  render() {
    const { orderId } = this.props;

    return (
      <ReturnEligibilityMessage
        orderId={orderId}
        isReturnEligible={isReturnEligible(orderId)}
        returnExipiration={getReturnExipiration(orderId)}
        paymentMethod={getPaymentMethod(orderId)}
        orderType={getOrderType(orderId)}
      />
    );
  }
}

export default OnlineReturnsEligibility;
