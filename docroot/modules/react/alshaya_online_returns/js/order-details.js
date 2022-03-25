import React from 'react';
import ReactDOM from 'react-dom';
import ReturnEligibility from './order-details/return-eligibility';
import isOnlineReturnsEnabled from '../../js/utilities/onlineReturnsHelper';

if (isOnlineReturnsEnabled() && document.querySelector('#online-returns-eligibility-window')) {
  ReactDOM.render(
    <ReturnEligibility />,
    document.querySelector('#online-returns-eligibility-window'),
  );
}
