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
   * Utility function to get return reasosn configurations.
   */
function getReturnConfigurationDetails() {
  let returnConfig = null;
  if (hasValue(drupalSettings.returnRequest)
      && hasValue(drupalSettings.returnRequest.returnConfig)
      && hasValue(drupalSettings.returnRequest.returnConfig.return_reasons)) {
    returnConfig = drupalSettings.returnRequest.returnConfig.return_reasons;
  }

  return returnConfig;
}

/**
   * Utility function to get default value for reasons select list.
   */
function getDefaultValueForReturnReasons() {
  const defaultReasons = [{
    value: 0,
    label: Drupal.t('Choose a reason'),
  }];

  return defaultReasons[0];
}

/**
   * Utility function to get default value for quantity select list.
   */
function getDefaultValueForQtyDropdown() {
  const defaultQtyOptions = [{
    value: 1,
    label: 1,
  }];

  return defaultQtyOptions;
}

/**
   * Utility function to pre-populate quantity select list.
   */
function populateQtyDropDownList(itemQuantity) {
  const qtyOptions = [];
  // Populate quanntity options for item quantities.
  for (let index = 1; index <= itemQuantity; index++) {
    qtyOptions.push({
      value: index,
      label: index,
    });
  }
  return qtyOptions;
}

export {
  getReturnConfigurationDetails,
  getDefaultValueForReturnReasons,
  getDefaultValueForQtyDropdown,
  populateQtyDropDownList,
  getOrderDetailsForReturnRequest,
};
