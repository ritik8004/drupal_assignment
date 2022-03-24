import React from 'react';
import ReactDOM from 'react-dom';
import OnlineReturnsEligibility from './my-account/online-returns-eligibility';
import isOnlineReturnsEnabled from '../../js/utilities/onlineReturnsHelper';

const orderId = document.querySelector('#online-returns-eligibility-window').closest('.recent__orders--body').getAttribute('data-order-id');
const selector = document.querySelector(`*[data-order-id="${orderId}"] #online-returns-eligibility-window`);

if (isOnlineReturnsEnabled() && selector) {
  ReactDOM.render(
    <OnlineReturnsEligibility orderId={orderId} />,
    selector,
  );
}
