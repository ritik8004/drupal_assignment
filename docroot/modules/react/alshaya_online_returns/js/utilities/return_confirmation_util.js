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

export default getOrderDetailsForReturnConfirmation;
