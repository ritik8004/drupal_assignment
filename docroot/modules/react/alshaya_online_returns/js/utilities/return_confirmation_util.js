import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Utility function to get order details for return request page.
 */
function getOrderDetailsForReturnConfirmation() {
  let orderDetails = null;
  if (hasValue(drupalSettings.returnInfo)
    && hasValue(drupalSettings.returnInfo.orderDetails)) {
    orderDetails = drupalSettings.returnInfo.orderDetails;
  }
  return orderDetails;
}

/**
 * Utility function to get return id for return confirmation page.
 */
function getReturnIdFromUrl() {
  const { search } = window.location;
  const params = new URLSearchParams(search);
  if (hasValue(params) && hasValue(params.get('rid'))) {
    return params.get('rid');
  }
  return null;
}

/**
 * Utility function to get strings for what's next section.
 */
function getReturnConfirmationStrings() {
  let returnConfirmationStrings = null;
  if (hasValue(drupalSettings.returnInfo)
    && hasValue(drupalSettings.returnInfo.returnConfirmationConfig)) {
    returnConfirmationStrings = drupalSettings.returnInfo.returnConfirmationConfig;
  }
  return returnConfirmationStrings;
}

export {
  getReturnIdFromUrl,
  getOrderDetailsForReturnConfirmation,
  getReturnConfirmationStrings,
};
