import React from 'react';
import ReactDOM from 'react-dom';
import OnlineReturnsEligibility from './my-account/online-returns-eligibility';

const returnWindowPlaceholder = document.querySelector('#online-returns-eligibility-window');

if (returnWindowPlaceholder) {
  const orderId = returnWindowPlaceholder.closest('.recent__orders--body').getAttribute('data-order-id');
  const selector = document.querySelector(`*[data-order-id="${orderId}"] #online-returns-eligibility-window`);

  if (selector) {
    ReactDOM.render(
      <OnlineReturnsEligibility orderId={orderId} />,
      selector,
    );
  }
}
