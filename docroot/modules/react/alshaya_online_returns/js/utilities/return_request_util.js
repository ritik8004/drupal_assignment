import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Utility function to get order details for return request page.
 */
function getOrderDetailsForReturnRequest() {
  let orderDetails = null;
  if (hasValue(drupalSettings.returnRequest)
      && hasValue(drupalSettings.returnRequest.orderDetails)) {
    orderDetails = drupalSettings.returnRequest.orderDetails;
  }

  return orderDetails;
}

/**
 * Utility function to get return reason options.
 */
function getReturnReasons() {
  // Setting default value for return reasons
  const returnReasons = [
    { value: 0, label: Drupal.t('Choose a reason') },
  ];
  if (hasValue(drupalSettings.returnRequest)
    && hasValue(drupalSettings.returnRequest.returnConfig)
    && hasValue(drupalSettings.returnRequest.returnConfig.return_reasons)) {
    // Populate reasons values from return reasons api call.
    const reasonsList = drupalSettings.returnRequest.returnConfig.return_reasons;
    Object.keys(reasonsList).forEach((key) => {
      returnReasons.push({
        value: reasonsList[key].id,
        label: reasonsList[key].label,
      });
    });
  }

  return returnReasons;
}

/**
 * Utility function to get values for quantity options.
 */
function getQuantityOptions(itemQtyOrdered) {
  const qtyOptions = [];
  if (itemQtyOrdered) {
    for (let i = 1; i <= itemQtyOrdered; i++) {
      qtyOptions.push({
        value: i,
        label: i,
      });
    }
  }
  return qtyOptions;
}

export {
  getReturnReasons,
  getQuantityOptions,
  getOrderDetailsForReturnRequest,
};
