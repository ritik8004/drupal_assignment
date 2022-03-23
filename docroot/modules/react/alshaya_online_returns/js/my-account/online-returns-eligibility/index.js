import React from 'react';
import ReactDOM from 'react-dom';
import ReturnWindow from '../return-window';
import ReturnAtStore from '../return-at-store';
import ReturnAction from '../return-action';
import {
  isReturnEligible,
  getReturnExipiration,
  getOrderType,
  getPaymentMethod,
  getReturnRequest,
  getReturnWindowClosedMessage,
  getReturnWindowOpenMessage,
} from '../../utilities/online_returns_util';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

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

  /**
   * Click event handler for the Return Items button.
   */
  handleOnClick = () => {
    const { orderId } = this.props;
    window.location.href = getReturnRequest(orderId);
  }

  render() {
    const { orderId } = this.props;

    if (!hasValue(orderId)) {
      return null;
    }

    const returnExipiration = getReturnExipiration(orderId);
    const paymentMethod = getPaymentMethod(orderId);

    if (returnExipiration > new Date()) {
      return <ReturnWindow message={getReturnWindowClosedMessage(returnExipiration)} />;
    }

    if (isReturnEligible(orderId)) {
      return (
        <>
          <ReturnWindow message={getReturnWindowOpenMessage(returnExipiration)} />
          <ReturnAction handleOnClick={this.handleOnClick} />
          <ReturnAtStore />
        </>
      );
    }

    if (getOrderType(orderId) === 'ship_to_store') {
      return (
        <>
          <ReturnWindow message={getReturnWindowOpenMessage(returnExipiration)} />
          <ReturnAction returnType="Click and Collect" />
          <ReturnAtStore returnType="Click and Collect" />
        </>
      );
    }

    return (
      <>
        <ReturnWindow message={getReturnWindowOpenMessage(returnExipiration)} />
        <ReturnAction returnType={paymentMethod} />
        <ReturnAtStore returnType={paymentMethod} />
      </>
    );
  }
}

export default OnlineReturnsEligibility;
