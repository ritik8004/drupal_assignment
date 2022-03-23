import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Helper function to check if order is eligible for return.
 */
function isReturnEligible(orderId) {
  let returnEligible = true;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].extension)
    && ({}).hasOwnProperty.call(drupalSettings.onlineReturns.recentOrders[orderId].extension, 'is_return_eligible')) {
    returnEligible = drupalSettings.onlineReturns.recentOrders[orderId]
      .extension.is_return_eligible;
  }

  return returnEligible;
}

/**
 * Helper function to get return expiration.
 */
function getReturnExipiration(orderId) {
  let returnExipiration = true;
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].extension)
    && ({}).hasOwnProperty.call(drupalSettings.onlineReturns.recentOrders[orderId].extension, 'return_exipiration')) {
    returnExipiration = new Date(
      drupalSettings.onlineReturns.recentOrders[orderId].extension.return_exipiration,
    );
  }

  return returnExipiration;
}

/**
 * Helper function to get order type.
 */
function getOrderType(orderId) {
  let orderType = '';
  if (hasValue(drupalSettings.onlineReturns)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId])
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].shipping)
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].shipping.extension_attributes)
    && ({}).hasOwnProperty.call(drupalSettings.onlineReturns.recentOrders[orderId].shipping.extension_attributes, 'click_and_collect_type')) {
    orderType = drupalSettings.onlineReturns.recentOrders[orderId]
      .shipping.extension_attributes.click_and_collect_type;
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
    && hasValue(drupalSettings.onlineReturns.recentOrders[orderId].extension)
    && ({}).hasOwnProperty.call(drupalSettings.onlineReturns.recentOrders[orderId].extension, 'payment_additional_info')) {
    paymentMethod = drupalSettings.onlineReturns.recentOrders[orderId].extension.payment_additional_info.filter((obj) => obj.key === 'method_title').shift();
  }

  return paymentMethod.value;
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
function getReturnRequest(orderId) {
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

export {
  isReturnEligible,
  getReturnExipiration,
  getOrderType,
  getPaymentMethod,
  formatDate,
  getReturnRequest,
  getReturnWindowClosedMessage,
  getReturnWindowOpenMessage,
};
