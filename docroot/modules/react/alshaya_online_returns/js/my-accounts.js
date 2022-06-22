import React from 'react';
import ReactDOM from 'react-dom';
import OnlineReturnsEligibility from './my-account/online-returns-eligibility';

document.addEventListener('onRecentOrderView', (orderDetails) => {
  const { orderId } = orderDetails.detail.data;
  const selector = document.querySelector(`*[data-order-id="${orderId}"] #online-returns-eligibility-window`);

  if (selector) {
    ReactDOM.render(
      <OnlineReturnsEligibility
        key={orderId}
        orderId={orderId}
        orderDetails={orderDetails}
        selector={selector}
      />,
      selector,
    );
  }
}, false);
