import React from 'react';
import ReactDOM from 'react-dom';
import isOnlineReturnsEnabled from '../../js/utilities/onlineReturnsHelper';
import ReturnInitiated from './order-details/return-initiated';
import ReturnedItemsListing from './order-details/returned-items-listing';
import { hasValue } from '../../js/utilities/conditionsUtility';
import { getReturns } from './utilities/order_details_util';

if (isOnlineReturnsEnabled()) {
  const returns = getReturns();

  if (returns instanceof Promise) {
    returns.then((returnResponse) => {
      if (hasValue(returnResponse)) {
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
