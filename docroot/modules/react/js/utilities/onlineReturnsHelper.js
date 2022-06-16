import { hasValue } from './conditionsUtility';
import {
  getReturnsByOrderId,
} from '../../alshaya_online_returns/js/utilities/return_api_helper';
import {
  getTotalRefundAmount,
} from '../../alshaya_online_returns/js/utilities/order_details_util';

/**
 * Helper function to check if Online Returns is enabled.
 */
export const isOnlineReturnsEnabled = () => hasValue(drupalSettings.onlineReturns);

/**
 * Helper function to get processed returns data for Order.
 *
 * @param orderEntityId
 * @param products
 * @returns {Promise<{}|{returns: *[], totalRefundAmount: StandardLonghandProperties.float}>}
 */
export const getProcessedReturnsData = async (orderEntityId, products) => {
  if (!hasValue(products)) {
    return {};
  }

  const returnResponse = await getReturnsByOrderId(orderEntityId);

  // Return early if no returns available for this order.
  if (!hasValue(returnResponse)
    || !hasValue(returnResponse.data)
    || !hasValue(returnResponse.data.items)) {
    return {};
  }

  const allReturns = [];

  // Looping through each return items.
  returnResponse.data.items.forEach((returnItem) => {
    const itemsData = [];
    const rejectedItemsData = [];
    returnItem.items.forEach((item) => {
      const productDetails = products.find((e) => e.item_id === item.order_item_id);
      if (!hasValue(productDetails)) {
        return;
      }

      // If return item id matches with order api responses and the item is not
      // rejected, we merge both the api responses and prepare complete product
      // data.
      const { qty_rejected: qtyRejected } = item.extension_attributes;
      if (qtyRejected > 0) {
        rejectedItemsData.push({ ...productDetails, returnData: item });
      } else {
        itemsData.push({ ...productDetails, returnData: item });
      }
    });

    // Here, returnInfo consists of return api related information
    // and items has all info related to products including return details
    // like how many quantities of item were returned.
    allReturns.push({
      returnInfo: returnItem,
      items: itemsData,
      rejectedItems: rejectedItemsData,
    });
  });

  return {
    returns: allReturns,
    totalRefundAmount: getTotalRefundAmount(returnResponse.data.items),
  };
};
