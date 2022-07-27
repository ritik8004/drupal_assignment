import { hasValue } from '../../../js/utilities/conditionsUtility';
import { getOrderDetails } from './online_returns_util';
import { getReturnedItems } from './return_confirmation_util';
import { getDeliveryAddress, getPaymentDetails, getReturnReasons } from './return_request_util';

/**
 * Utility function to get order GTM info.
 *
 * @return {Object}
 *   An object containing GTM info about the order.
 */
function getOrderGtmInfo() {
  if (hasValue(drupalSettings.returnInfo)
    && hasValue(drupalSettings.returnInfo.orderDetails)
    && hasValue(drupalSettings.returnInfo.orderDetails['#gtm_info'])) {
    return drupalSettings.returnInfo.orderDetails['#gtm_info'];
  }

  // For order detail page, get the data from onlineReturns drupal settings.
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.gtm_info)) {
    return drupalSettings.onlineReturns.gtm_info;
  }

  return {};
}

/**
 * Util function to get the product return reason text.
 *
 * @param {string} returnReasonCode
 *   The return reason code.
 *
 * @return {string}
 *   The return reason text.
 */
function getReturnReasonText(returnReasonCode) {
  const returnReasons = getReturnReasons();
  // if return reason is not empty.
  if (returnReasons.length > 0) {
    // Filter out the object which has been selected by the user.
    const selectedReturnReason = returnReasons.filter((item) => item.value === returnReasonCode);
    if (selectedReturnReason) {
      return selectedReturnReason[0].label;
    }
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
  const skuProduct = {};
  itemsSelected.forEach((item) => {
    const gtmInfo = getOrderGtmInfo();
    if (gtmInfo && hasValue(gtmInfo.products) && hasValue(gtmInfo.products[item.sku])) {
      // Push the return reason and qty returned for individual item.
      if (hasValue(item.reason)) {
        gtmInfo.products[item.sku].reason = getReturnReasonText(item.reason);
      } else if (hasValue(item.returnData)
        && hasValue(item.returnData.reason)) {
        gtmInfo.products[item.sku].reason = getReturnReasonText(Number(item.returnData.reason));
      }

      if (hasValue(item.qty_requested)) {
        gtmInfo.products[item.sku].quantity = item.qty_requested;
      } else if (hasValue(item.returnData)
        && hasValue(item.returnData.qty_requested)) {
        gtmInfo.products[item.sku].quantity = item.returnData.qty_requested;
      }
      // Traverse all the object items and store them in a separate array.
      Object.keys(gtmInfo.products[item.sku]).forEach((key) => {
        if (!skuProduct[key]) {
          skuProduct[key] = [];
        }
        // Push all the required info skuProduct.
        if (gtmInfo.products[item.sku][key]) {
          skuProduct[key].push(gtmInfo.products[item.sku][key]);
        }
      });
    }
  });

  // Convert the array of all the attributes into single product object.
  Object.keys((skuProduct)).forEach((key) => {
    // Join only if the value is not empty.
    if (skuProduct[key].length > 0) {
      skuProduct[key] = skuProduct[key].join('|');
    } else {
      skuProduct[key] = '';
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
 * @param {object} returnInfo
 *   The object containing return information.
 *
 * @returns {object}
 *   The return order GTM object.
 */
function getPreparedOrderGtm(eventType, returnInfo) {
  const gtmInfo = getOrderGtmInfo();
  let returnOrder = {};

  // Check if general GTM info is present or not.
  if (hasValue(gtmInfo.general)) {
    const {
      transactionId,
      deliveryOption,
    } = gtmInfo.general;

    // Prepare the Return order object.
    returnOrder = {
      orderTransactionId: transactionId,
      orderType: deliveryOption,
      // @todo Will done in DIG-10167.
      orderFirstTimeTransaction: '',
    };
  }

  const orderDetails = getOrderDetails();

  // Get delivery address info.
  const deliveryInfo = getDeliveryAddress(orderDetails);
  // Get the payment details.
  let paymentDetails = getPaymentDetails(orderDetails);
  // Sort the payment details based on the weight in ascending order.
  paymentDetails = Object.values(paymentDetails).sort((p1, p2) => p1.weight - p2.weight);

  // Combine all the payment methods.
  const paymentMethods = [];
  if (Object.keys(paymentDetails).length > 0) {
    Object.keys(paymentDetails).forEach((index) => {
      paymentMethods.push(paymentDetails[index].card_type);
    });
  }

  // Prepare the Return order object.
  if (deliveryInfo) {
    returnOrder.orderDeliveryCity = deliveryInfo.area_parent_display;
    returnOrder.orderDeliveryArea = deliveryInfo.administrative_area_display;
  }
  // This will always be online in our case.
  returnOrder.returnType = 'online';

  // Add returned & refunded info for selected events.
  if (eventType !== 'item_confirmed' && returnInfo) {
    // Calculate the refund amount based on the qty returned and individual item
    // amount/discounted amount.
    const returnedItems = getReturnedItems(returnInfo);

    // If returned items is empty then get it from filtered from onlineReturns
    // drupalSettings.
    if (!hasValue(returnedItems.length) && hasValue(drupalSettings.onlineReturns)
      && hasValue(drupalSettings.onlineReturns.products)) {
      drupalSettings.onlineReturns.products.forEach((item) => {
        const returnData = returnInfo.items.find(
          (returnItem) => item.item_id === returnItem.order_item_id,
        );
        // Store the product only if the item codes are same.
        if (returnData) {
          returnedItems.push(Object.assign(item, { returnData }));
        }
      });
    }

    let refundAmount = 0;
    returnedItems.forEach((item) => {
      if (hasValue(item.returnData)) {
        refundAmount += (item.returnData.qty_requested * item.price_incl_tax);
      }
    });

    returnOrder.refundAmount = refundAmount;
    returnOrder.refundMethods = paymentMethods.length > 0 ? paymentMethods.join('_') : '';
    returnOrder.returnId = returnInfo.increment_id;
    // @Todo To add the firstTimeReturn info when available.
    returnOrder.firstTimeReturn = '';
  }

  return returnOrder;
}

export {
  getOrderGtmInfo,
  getPreparedOrderGtm,
  getProductGtmInfo,
  getReturnReasonText,
};
