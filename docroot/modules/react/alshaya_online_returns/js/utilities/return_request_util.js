import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Utility function to get return reason options.
 */
function getReturnReasons(langcode = 'en') {
  // Setting default value for return reasons
  const returnReasons = [
    { value: 0, label: Drupal.t('Choose a reason', {}, { context: 'online_returns' }) },
  ];
  let reasonsList = [];
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.returnInfo)
    && hasValue(drupalSettings.onlineReturns.returnInfo.returnConfig)
    && hasValue(drupalSettings.onlineReturns.returnInfo.returnConfig[langcode])
    && hasValue(drupalSettings.onlineReturns.returnInfo.returnConfig[langcode].return_reasons)) {
    // Populate reasons values from return reasons api call.
    reasonsList = drupalSettings.onlineReturns.returnInfo.returnConfig[langcode].return_reasons;
  } else if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.return_config)
    && hasValue(drupalSettings.onlineReturns.return_config[langcode])
    && hasValue(drupalSettings.onlineReturns.return_config[langcode].return_reasons)) {
    reasonsList = drupalSettings.onlineReturns.return_config[langcode].return_reasons;
  }

  Object.keys(reasonsList).forEach((key) => {
    returnReasons.push({
      value: reasonsList[key].id,
      label: reasonsList[key].label,
    });
  });

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

/**
 * Utility function to get delivery address data from order details.
 */
function getDeliveryAddress(orderDetails) {
  let deliveryAddress = {};
  if (hasValue(orderDetails)
    && hasValue(orderDetails['#order_details'])
    && hasValue(orderDetails['#order_details'].delivery_address_raw)) {
    deliveryAddress = orderDetails['#order_details'].delivery_address_raw;
  }
  return deliveryAddress;
}

/**
 * Utility function to get payment data from order details.
 */
function getPaymentDetails(orderDetails) {
  let paymentDetails = {};
  if (hasValue(orderDetails)
    && hasValue(orderDetails['#order_details'])
    && hasValue(orderDetails['#order_details'].paymentDetails)) {
    paymentDetails = orderDetails['#order_details'].paymentDetails;
  }
  return paymentDetails;
}

/**
 * Utility function to check whether to add checkbox to return item or not.
 */
function addCheckboxToReturnItem(item) {
  let addCheckbox = true;
  if (!hasValue(item.is_returnable)
    || hasValue(item.is_big_ticket)
    || !hasValue(item.qty_ordered)) {
    addCheckbox = false;
  }

  return addCheckbox;
}

/**
 * Utility function to get return resolutions.
 */
function getReturnResolutions() {
  let resolutions = [];
  const langcode = drupalSettings.path.currentLanguage;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.returnInfo)
    && hasValue(drupalSettings.onlineReturns.returnInfo.returnConfig)
    && hasValue(drupalSettings.onlineReturns.returnInfo.returnConfig[langcode])
    && hasValue(drupalSettings.onlineReturns.returnInfo.returnConfig[langcode].resolutions)) {
    resolutions = drupalSettings.onlineReturns.returnInfo.returnConfig[langcode].resolutions;
  } else if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.return_config)
    && hasValue(drupalSettings.onlineReturns.return_config[langcode])
    && hasValue(drupalSettings.onlineReturns.return_config[langcode].resolutions)) {
    resolutions = drupalSettings.onlineReturns.return_config[langcode].resolutions;
  }

  return resolutions;
}

/**
 * Utility function to get default return resolution.
 */
function getDefaultResolutionId() {
  let defaultResolution = null;
  const resolutions = getReturnResolutions();

  if (hasValue(resolutions)) {
    defaultResolution = resolutions.filter((resolution) => resolution.label === 'Refund');
  }

  return hasValue(defaultResolution) ? defaultResolution.shift().id : '';
}

export {
  getReturnReasons,
  getQuantityOptions,
  getDeliveryAddress,
  getPaymentDetails,
  addCheckboxToReturnItem,
  getDefaultResolutionId,
};
