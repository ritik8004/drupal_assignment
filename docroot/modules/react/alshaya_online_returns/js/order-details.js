import React from 'react';
import ReactDOM from 'react-dom';
import ReturnEligibility from './order-details/return-eligibility';
import isOnlineReturnsEnabled from '../../js/utilities/onlineReturnsHelper';
import ReturnInitiated from './order-details/return-initiated';
import ReturnedItemsListing from './order-details/returned-items-listing';
import { hasValue } from '../../js/utilities/conditionsUtility';
import getProcessedReturnsData from './utilities/return_eligibility_util';

if (isOnlineReturnsEnabled()) {
  const { orderEntityId } = drupalSettings.onlineReturns;
  const returns = getProcessedReturnsData(orderEntityId, 'order_detail');

  if (returns instanceof Promise) {
    returns.then((returnResponse) => {
      if (hasValue(returnResponse)) {
        if (document.querySelector('#online-returns-eligibility-window')) {
          ReactDOM.render(
            <ReturnEligibility returns={returnResponse} />,
            document.querySelector('#online-returns-eligibility-window'),
          );
        }
        if (document.querySelector('#online-return-initiated')) {
          ReactDOM.render(
            <ReturnInitiated returns={returnResponse} />,
            document.querySelector('#online-return-initiated'),
          );
        }

        if (document.querySelector('#online-returned-items')) {
          ReactDOM.render(
            <ReturnedItemsListing returns={returnResponse} />,
            document.querySelector('#online-returned-items'),
          );
        }
      }
    });
  }
}
