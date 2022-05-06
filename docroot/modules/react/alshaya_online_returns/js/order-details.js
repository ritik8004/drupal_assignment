import React from 'react';
import ReactDOM from 'react-dom';
import ReturnEligibility from './order-details/return-eligibility';
import isOnlineReturnsEnabled from '../../js/utilities/onlineReturnsHelper';
import ReturnInitiated from './order-details/return-initiated';
import ReturnedItemsListing from './order-details/returned-items-listing';
import { showFullScreenLoader, removeFullScreenLoader } from '../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../js/utilities/conditionsUtility';
import { processReturnData } from './utilities/order_details_util';
import { getReturnsByOrderId } from './utilities/return_api_helper';

/**
 * Method to handle the modal on load event and render component.
 */
const getReturns = async () => {
  const { orderEntityId } = drupalSettings.onlineReturns;
  showFullScreenLoader();
  const returnData = await getReturnsByOrderId(orderEntityId);
  removeFullScreenLoader();
  // @todo: Get return status and return message from api call.
  if (hasValue(returnData) && hasValue(returnData.data) && hasValue(returnData.data.items)) {
    const returns = processReturnData(returnData.data.items);
    return returns;
  }
  return null;
};

if (isOnlineReturnsEnabled()) {
  const returns = getReturns();

  if (document.querySelector('#online-returns-eligibility-window')) {
    ReactDOM.render(
      <ReturnEligibility />,
      document.querySelector('#online-returns-eligibility-window'),
    );
  }

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
