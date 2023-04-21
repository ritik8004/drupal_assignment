import moment from 'moment-timezone';
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Helper function to check if order is eligible for return.
 */
function isReturnEligible(orderId) {
  let returnEligible = true;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])) {
    returnEligible = drupalSettings.onlineReturns.recentOrders[orderId].isReturnEligible;
  }

  return returnEligible;
}

/**
 * Helper function to get return expiration.
 */
function getReturnExpiration(orderId) {
  let returnExpiration = null;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].returnExpiration)) {
    returnExpiration = drupalSettings.onlineReturns.recentOrders[orderId].returnExpiration;
  }

  return returnExpiration;
}

/**
 * Helper function to get order type.
 */
function getOrderType(orderId) {
  let orderType = '';
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].orderType)) {
    orderType = drupalSettings.onlineReturns.recentOrders[orderId].orderType;
  }

  return orderType;
}

/**
 * Helper function to get order payment method.
 */
function getPaymentMethod(orderId) {
  let paymentMethod = null;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].paymentMethod)) {
    paymentMethod = drupalSettings.onlineReturns.recentOrders[orderId].paymentMethod;
  }

  return paymentMethod;
}

/**
 * Utility function to format date.
 */
function formatDate(date) {
  // eg. 02-Feb-2021
  return new Date(date.replace(/ /g, 'T')).toLocaleString(
    drupalSettings.path.currentLanguage,
    { day: '2-digit', month: 'short', year: 'numeric' },
  );
}

/**
 * Utility function to format date time in 30 Nov. 2016 @ 20h55.
 */
function formatDateTime(date) {
  // Setting default value for date format.
  // It can be changed via config object.
  let formattedDate = null;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.returnInfo)
    && hasValue(drupalSettings.onlineReturns.returnInfo.dateFormat)) {
    const { dateFormat, timeZone } = drupalSettings.onlineReturns.returnInfo;
    formattedDate = moment.utc(date).tz(timeZone).format(dateFormat || 'DD MMM YYYY @H[h]mm');
  }
  return formattedDate;
}

/**
 * Utility function to get return request url.
 */
function getReturnRequestUrl(orderId) {
  return Drupal.url(`user/${drupalSettings.user.uid}/order/${orderId}/return`);
}

/**
 * Utility function to get return window closed message.
 */
function getReturnWindowClosedMessage(date) {
  const message = Drupal.t('Return window closed on @date',
    { '@date': formatDate(date) },
    { context: 'online_returns' });
  return message;
}

/**
 * Utility function to get return window not active message.
 */
function getReturnWindowEligibleDateMessage(date) {
  const message = Drupal.t('Eligible for return until @date',
    { '@date': formatDate(date) },
    { context: 'online_returns' });
  return message;
}

/**
 * Utility function to get return window open message.
 */
function getReturnWindowOpenMessage(date) {
  const message = Drupal.t('You have until @date to return the items',
    { '@date': formatDate(date) },
    { context: 'online_returns' });
  return message;
}

/**
 * Helper function to check if return window is closed.
 */
function isReturnWindowClosed(date) {
  let dateStr = date;

  // Temporary fix to split the date/time string and capture only the date;
  const split = dateStr.split(' ');
  if (Array.isArray(split)) {
    // Keep only the date.
    [dateStr] = split;
  }

  // Get UTC dates.
  const expireDate = moment.utc(dateStr).endOf('day');
  const currDate = moment.utc();

  return (currDate.valueOf() > expireDate.valueOf());
}

/**
 * Helper function to check if any active return exists.
 */
function hasActiveReturns(returns) {
  if (hasValue(returns)
    && returns.some((item) => item.returnInfo.extension_attributes.is_closed === false)) {
    return true;
  }
  return false;
}

/**
 * Utility function to get return confirmation url.
 */
function getReturnConfirmationUrl(orderId, returnId) {
  // Get user details from session.
  const { userEmailID } = drupalSettings.userDetails;
  // Making returd id more secure with multiple details.
  if (hasValue(userEmailID) && drupalSettings.user.uid !== 0) {
    const secureReturnId = btoa(JSON.stringify({
      return_id: returnId,
      email: userEmailID,
    }));
    return Drupal.url(`user/${drupalSettings.user.uid}/order/${orderId}/return-confirmation?rid=${secureReturnId}`);
  }
  return null;
}

/**
 * Utility function to get return confirmation url.
 */
function getOrderDetailsUrl(orderId) {
  if (hasValue(orderId) && drupalSettings.user.uid !== 0) {
    return Drupal.url(`user/${drupalSettings.user.uid}/order/${orderId}`);
  }
  return null;
}

/**
 * Utility function to get address data.
 */
function getAdressData(shippingAddress) {
  if (!hasValue(drupalSettings.address_fields) || !hasValue(shippingAddress)) {
    return null;
  }

  const addressData = [];
  // Add country label to address item array.
  if (hasValue(shippingAddress.country_label)) {
    addressData.push(shippingAddress.country_label);
  }
  // Populate address field with each key item.
  Object.keys(drupalSettings.address_fields).forEach((key) => {
    if (hasValue(shippingAddress[key])) {
      let fillVal = shippingAddress[key];
      if (key === 'administrative_area') {
        fillVal = shippingAddress.administrative_area_display;
      } else if (key === 'area_parent') {
        fillVal = shippingAddress.area_parent_display;
      }
      addressData.push(fillVal);
    }
  });
  return addressData;
}

/**
 * Utility function to get order details for return pages.
 */
const getOrderDetails = () => window.commerceBackend.getOrderDetails();

/**
 * Utility function to get cancel button status.
 *
 * @param {Object} returnData
 *   The return data.
 *
 * @returns {boolean}
 *   True if button should be visible or False otherwise.
 */
function getCancelButtonStatus(returnData) {
  const {
    is_picked: isPicked,
    is_closed: isClosed,
  } = returnData.returnInfo.extension_attributes;
  // Hide Cancel button when return is picked up or closed.
  if (hasValue(isPicked) || hasValue(isClosed)) {
    return false;
  }

  return true;
}

/**
 * Utility function to get print label button status.
 */
function getPrintLabelStatus(returnData) {
  const {
    awb_path: AwbPath,
    is_picked: isPicked,
    is_closed: isClosed,
  } = returnData.returnInfo.extension_attributes;
    // Set the `showPrintLabelBtn` to true if awb path is available.
  if (hasValue(AwbPath)
      && !hasValue(isPicked)
      && !hasValue(isClosed)) {
    return true;
  }
  return false;
}

/**
 * Helper function to check if order contains big ticket items or not.
 */
function isBigTicketOrder(orderId) {
  let bigTicketOrder = false;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].isBigTicketOrder)) {
    bigTicketOrder = drupalSettings.onlineReturns.recentOrders[orderId].isBigTicketOrder;
  }
  return bigTicketOrder;
}

/**
 * Utility function to get customer service number.
 */
function getCustomerServiceNumber() {
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.customerServiceNumber)) {
    return drupalSettings.onlineReturns.customerServiceNumber;
  }
  return '';
}

export {
  isReturnEligible,
  getReturnExpiration,
  getOrderType,
  getPaymentMethod,
  formatDate,
  getReturnRequestUrl,
  getReturnWindowClosedMessage,
  getReturnWindowOpenMessage,
  isReturnWindowClosed,
  getReturnConfirmationUrl,
  getOrderDetailsUrl,
  getAdressData,
  formatDateTime,
  getOrderDetails,
  hasActiveReturns,
  getReturnWindowEligibleDateMessage,
  getPrintLabelStatus,
  getCancelButtonStatus,
  isBigTicketOrder,
  getCustomerServiceNumber,
};
