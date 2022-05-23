
import { hasValue } from '../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../js/utilities/showRemoveFullScreenLoader';
import { processReturnData } from './order_details_util';
import { getReturnsByOrderId } from './return_api_helper';

/**
 * Method to get returns and process the data.
 */
const getProcessedReturnsData = async (orderEntityId, context) => {
  showFullScreenLoader();
  const returnData = await getReturnsByOrderId(orderEntityId);
  removeFullScreenLoader();
  if (hasValue(returnData) && hasValue(returnData.data) && hasValue(returnData.data.items)) {
    const returns = processReturnData(returnData.data.items, context);
    return returns;
  }
  return null;
};

/**
 * Method to handle the modal on load event and render component.
 */
const getReturns = async () => {
  const { orderEntityId } = drupalSettings.onlineReturns;
  const returns = await getProcessedReturnsData(orderEntityId, 'order_detail');
  if (hasValue(returns)) {
    return returns;
  }
  return null;
};

export {
  getReturns,
  getProcessedReturnsData,
};
