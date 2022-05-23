
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

export default getProcessedReturnsData;
