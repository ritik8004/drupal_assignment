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
      if ((shippingMethod.carrier_code === 'SAMEDAY') || (shippingMethod.carrier_code === 'EXPRESS')) {
        count += 1;
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
 * any product in cart.
 */
const checkAreaAvailabilityStatusOnCart = (cartShippingMethods) => {
  let show = true;
  if (typeof cartShippingMethods !== 'undefined') {
    cartShippingMethods.forEach((cartShippingMethodsList) => {
      const shippingMethods = cartShippingMethodsList.applicable_shipping_methods;
      const status = checkShippingMethodsStatus(shippingMethods);
      if (status !== true) {
        show = false;
      }
    });
    if (show === false) {
      return show;
    }
  }
  return true;
};

export {
  isExpressDeliveryEnabled,
  checkProductExpressDeliveryStatus,
  checkShippingMethodsStatus,
  checkAreaAvailabilityStatusOnCart,
};
