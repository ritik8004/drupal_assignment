import { hasValue } from './conditionsUtility';

/**
 * Helper function to check if Express delivery is enabled.
 */
const isExpressDeliveryEnabled = () => {
  if (typeof drupalSettings.expressDelivery !== 'undefined'
    && typeof drupalSettings.expressDelivery.enabled !== 'undefined') {
    return drupalSettings.expressDelivery.enabled;
  }

  return false;
};

/**
 * Helper function to check if Express delivery applicable for sku.
 */
const checkProductExpressDeliveryStatus = (sku) => {
  if (typeof drupalSettings.productInfo !== 'undefined'
    && typeof drupalSettings.expressDelivery !== 'undefined'
    && typeof drupalSettings.productInfo[sku].expressDelivery !== 'undefined') {
    return drupalSettings.productInfo[sku].expressDelivery;
  }

  return false;
};

/**
 * Helper function to check if ED/SDD is present
 * in the shipping methods array.
 */
const checkShippingMethodsStatus = (shippingMethods) => {
  let count = 0;
  if (typeof shippingMethods !== 'undefined') {
    shippingMethods.forEach((shippingMethod) => {
      if (shippingMethod.available !== 'undefined' && shippingMethod.available) {
        if ((shippingMethod.carrier_code === 'SAMEDAY') || (shippingMethod.carrier_code === 'EXPRESS')) {
          count += 1;
        }
      }
    });
    if (count > 0) {
      return true;
    }
  }

  return false;
};

/**
 * Helper function to check if ED/SDD is disabled for
 * all the products in cart.
 */
const checkAreaAvailabilityStatusOnCart = (cartShippingMethods) => {
  let show = false;
  if (typeof cartShippingMethods !== 'undefined' && !hasValue(cartShippingMethods.error)) {
    cartShippingMethods.forEach((cartShippingMethodsList) => {
      const shippingMethods = cartShippingMethodsList.applicable_shipping_methods;
      const status = checkShippingMethodsStatus(shippingMethods);
      if (status === true) {
        show = true;
      }
    });
    return show;
  }
  return true;
};

/**
 * Helper function to check if Express delivery is enabled.
 */
const checkSameDayDeliveryStatus = () => {
  if (typeof drupalSettings.expressDelivery !== 'undefined'
    && typeof drupalSettings.expressDelivery.same_day_delivery !== 'undefined') {
    return drupalSettings.expressDelivery.same_day_delivery;
  }

  return false;
};

/**
 * Helper function to check if Express delivery is enabled.
 */
const checkExpressDeliveryStatus = () => {
  if (typeof drupalSettings.expressDelivery !== 'undefined'
    && typeof drupalSettings.expressDelivery.express_delivery !== 'undefined') {
    return drupalSettings.expressDelivery.express_delivery;
  }

  return false;
};

export {
  isExpressDeliveryEnabled,
  checkProductExpressDeliveryStatus,
  checkShippingMethodsStatus,
  checkAreaAvailabilityStatusOnCart,
  checkSameDayDeliveryStatus,
  checkExpressDeliveryStatus,
};
