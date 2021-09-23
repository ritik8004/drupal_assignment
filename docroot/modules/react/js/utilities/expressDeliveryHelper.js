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

export {
  isExpressDeliveryEnabled,
  checkProductExpressDeliveryStatus,
};
