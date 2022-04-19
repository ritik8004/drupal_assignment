import React from 'react';
import ReactDOM from 'react-dom';
import ReturnEligibility from './order-details/return-eligibility';
import isOnlineReturnsEnabled from '../../js/utilities/onlineReturnsHelper';
import ReturnedItemsListing from './order-details/returned-items-listing';

if (isOnlineReturnsEnabled()) {
  if (document.querySelector('#online-returns-eligibility-window')) {
    ReactDOM.render(
      <ReturnEligibility />,
      document.querySelector('#online-returns-eligibility-window'),
    );
  }

  if (document.querySelector('#online-returned-items')) {
    ReactDOM.render(
      <ReturnedItemsListing />,
      document.querySelector('#online-returned-items'),
    );
  }
}
