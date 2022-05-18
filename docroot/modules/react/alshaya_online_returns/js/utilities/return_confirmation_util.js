import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Utility function to get return id for return confirmation page.
 */
function getReturnIdFromUrl() {
  const { search } = window.location;
  const params = new URLSearchParams(search);
  if (hasValue(params) && hasValue(params.get('rid'))) {
    const returnJsonObj = JSON.parse(atob(params.get('rid')));
    return returnJsonObj.return_id;
  }
  return null;
}

/**
 * Utility function to get details of all the returned items.
 */
function getReturnedItems(returnData) {
  const returnedItems = [];
  if (hasValue(drupalSettings.returnInfo)
    && hasValue(drupalSettings.returnInfo.orderDetails)
    && hasValue(drupalSettings.returnInfo.orderDetails['#products'])) {
    // Filter out returned items from order details array.
    const allProducts = drupalSettings.returnInfo.orderDetails['#products'];
    const returnedItemsRaw = returnData.items;
    returnedItemsRaw.forEach((returnItem) => {
      const productDetails = allProducts.find((item) => item.item_id === returnItem.order_item_id);
      const mergedItem = Object.assign(productDetails, { returnData: returnItem });
      returnedItems.push(mergedItem);
    });
  }
  return returnedItems;
}

/**
 * Utility function to get strings for what's next section.
 */
function getReturnConfirmationStrings() {
  let returnConfirmationStrings = null;
  if (hasValue(drupalSettings.returnInfo)
    && hasValue(drupalSettings.returnInfo.returnConfirmationStrings)) {
    returnConfirmationStrings = drupalSettings.returnInfo.returnConfirmationStrings;
  }
  return returnConfirmationStrings;
}

export {
  getReturnIdFromUrl,
  getReturnConfirmationStrings,
  getReturnedItems,
};
