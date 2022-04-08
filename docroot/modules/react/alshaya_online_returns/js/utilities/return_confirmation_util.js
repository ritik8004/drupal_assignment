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

function getReturnIdFromUrl() {
  const { search } = window.location;
  const params = new URLSearchParams(search);
  if (hasValue(params) && hasValue(params.get('returnId'))) {
    return params.get('returnId');
  }
  return null;
}

export {
  getReturnIdFromUrl,
  getOrderDetailsForReturnConfirmation,
};
