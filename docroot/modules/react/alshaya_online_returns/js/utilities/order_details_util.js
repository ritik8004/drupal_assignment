/* eslint-disable */
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Utility function to process return data.
 */
function processReturnData(returns, context = 'order_detail') {
  const allReturns = [];

  returns.forEach((returnItem) => {
    let itemsData = [];
    // We need product items on order details page.
    if (context === 'order_detail') {
      returnItem.items.forEach((item) => {
        const productDetails = drupalSettings.onlineReturns.products.find((element) => {
          return element.item_id === item.order_item_id;
        });

        if (hasValue(productDetails)) {
          const mergedItem = Object.assign(productDetails, {returnData: item});
          itemsData.push(mergedItem);
        }
      });
    }

    const returnData = {
      returnInfo: returnItem,
      items: itemsData,
    };

    allReturns.push(returnData);
  });

  return allReturns;
}

/**
 * Utility function to return the type of return.
 *
 * @param {object} returnItem
 *   The individual return item object.
 *
 * @returns {string}
 *   A string to tell the type of return.
 */
function getTypeFromReturnItem(returnItem) {
  // Based on the `is_online` flag we will identify the type of return.
  if (returnItem.returnInfo.extension_attributes.is_online) {
    return 'online';
  } else {
    return 'store';
  }
}

export {
  processReturnData,
  getTypeFromReturnItem,
};
