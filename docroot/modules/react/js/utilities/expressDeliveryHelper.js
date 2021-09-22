/**
 * Helper function to check if Express delivery is enabled.
 */
const isExpressDeliveryEnabled = () => {
  if (typeof drupalSettings.expressDeliveryEnabled !== 'undefined') {
    return drupalSettings.expressDeliveryEnabled;
  }

  return false;
};

/**
 * Helper function to check if Express delivery applicable for sku.
 */
const checkProductExpressDeliveryStatus = (sku) => {
  if (typeof drupalSettings.productInfo !== 'undefined'
    && typeof drupalSettings.productInfo[sku].express_delivery !== 'undefined') {
    return drupalSettings.productInfo[sku].express_delivery;
  }

  return false;
};

export {
  isExpressDeliveryEnabled,
  checkProductExpressDeliveryStatus,
};
