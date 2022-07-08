import { hasValue } from '../../../js/utilities/conditionsUtility';
import { getOrderDetails } from './online_returns_util';
import { getDeliveryAddress } from './return_request_util';

/**
 * Utility function to get order GTM info.
 */
function getOrderGtmInfo() {
  if (hasValue(drupalSettings.returnInfo)
    && hasValue(drupalSettings.returnInfo.orderDetails)
    && hasValue(drupalSettings.returnInfo.orderDetails['#gtm_info'])) {
    return drupalSettings.returnInfo.orderDetails['#gtm_info'];
  }

  return '';
}

/**
 * Get the product array of all the selected items.
 *
 * @param {array} itemsSelected
 *   An array of all the selected products for return.
 *
 * @returns {array}
 *   An array containing sku info of all the selected products.
 */
function getProductGtmInfo(itemsSelected) {
  // Build the product SKU object for all the selected items.
  const skuProduct = [];
  itemsSelected.forEach((item) => {
    const GtmInfo = getOrderGtmInfo();
    if (GtmInfo && hasValue(GtmInfo.products) && hasValue(GtmInfo.products[item.sku])) {
      // Push the return reason for individual item.
      GtmInfo.products[item.sku].reason = item.reason;
      skuProduct.push(GtmInfo.products[item.sku]);
    }
  });

  return skuProduct;
}

/**
 * Get the parepared order object for the return GTM.
 *
 * @param {string} eventType
 *   The type of event which is getting performed.
 *
 * @returns {object}
 *   The return order GTM object.
 */
function getPreparedOrderGtm(eventType) {
  const gtmInfo = getOrderGtmInfo();
  const {
    transactionId,
    deliveryOption,
    firstTimeTransaction,
  } = gtmInfo.general;

  // Get delivery address info.
  const deliveryInfo = getDeliveryAddress(getOrderDetails());

  // Prepare the Return order object.
  const returnOrder = {
    orderTransactionId: transactionId,
    orderType: deliveryOption,
    orderFirstTimeTransaction: firstTimeTransaction,
    orderDeliveryCity: deliveryInfo.area_parent_display,
    orderDeliveryArea: deliveryInfo.administrative_area_display,
    // This will always be online in our case.
    returnType: 'online',
  };

  // @todo to update the info in the further GA tickets.
  // Add returned & refunded info for selected events.
  if (eventType !== 'item_confirmed') {
    returnOrder.refundAmount = '';
    returnOrder.refundMethods = '';
    returnOrder.returnId = '';
    // @Todo To add the firstTimeReturn info when available.
    returnOrder.firstTimeReturn = '';
  }

  return returnOrder;
}

export {
  getOrderGtmInfo,
  getPreparedOrderGtm,
  getProductGtmInfo,
};
