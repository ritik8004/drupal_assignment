import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Helper function to check if order is eligible for return.
 */
function isReturnEligible(orderId) {
  let returnEligible = true;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].isReturnEligible)) {
    returnEligible = drupalSettings.onlineReturns.recentOrders[orderId].isReturnEligible;
  }

  return returnEligible;
}

/**
 * Helper function to get return expiration.
 */
function getReturnExpiration(orderId) {
  let returnExpiration = true;
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
  return new Date(date).toLocaleString(
    drupalSettings.path.currentLanguage,
    { day: '2-digit', month: 'short', year: 'numeric' },
  );
}

/**
 * Utility function to get return request url.
 */
function getReturnRequestUrl(orderId) {
  const url = Drupal.url(`user/${drupalSettings.user.uid}/order/${orderId}/return`);
  return url;
}

/**
 * Utility function to get return window closed message.
 */
function getReturnWindowClosedMessage(date) {
  const message = Drupal.t('Return window closed on @date', {
    '@date': formatDate(date),
  });
  return message;
}

/**
 * Utility function to get return window open message.
 */
function getReturnWindowOpenMessage(date) {
  const message = Drupal.t('You have untill @date to return the items', {
    '@date': formatDate(date),
  });
  return message;
}

/**
 * Helper function to check if return window is closed.
 */
function isReturnWindowClosed(date) {
  return (new Date(date) < new Date());
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
};
