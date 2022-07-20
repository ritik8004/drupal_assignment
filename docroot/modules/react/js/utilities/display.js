/**
 * Returns if configurable boxes should be shown for config product attribtues.
 *
 * @returns boolean
 *   Whether to show configurable boxes or not. Returns true by default if the
 * drupal settings is empty or null.
 */
const isDisplayConfigurableBoxes = () => (
  drupalSettings.show_configurable_boxes_after !== null
  && drupalSettings.show_configurable_boxes_after !== ''
    ? window.innerWidth > drupalSettings.show_configurable_boxes_after
    : true
);

/**
 * Returns the buyable status for a product.
 *
 * @param boolean productBuyable
 *   Flag to indicate if the particular product is buyable.
 *
 * @returns boolean
 *   If the product is buyable or not.
 *
 * @see alshaya_acm_product_is_buyable().
 */
const isProductBuyable = (productBuyable) => (
  drupalSettings.checkoutFeatureStatus === 'enabled'
  && (drupalSettings.is_all_products_buyable || productBuyable)
);

/**
 * Returns the array of hidden/lpn form attribute names.
 *
 * @returns array
 *  The array of hidden form attribute names.
 */
const getHiddenFormAttributes = () => (typeof drupalSettings.lpn !== 'undefined'
  ? drupalSettings.lpn.lpn_attribute
  : []);

/**
 * Returns the filtered product attribute object.
 *
 * @param {object} product
 *   The individual product object.
 *
 * @returns object
 *   Filterd product attribute object.
 */
const getFilteredProductAttributes = (product) => {
  const hiddenAttirbutes = getHiddenFormAttributes();
  if (hiddenAttirbutes.length > 0) {
    return (
      Object.fromEntries(Object.entries(product.attributes).filter(
        ([key]) => !key.includes(hiddenAttirbutes),
      ))
    );
  }

  return product.attributes;
};

/**
 * Returns the allowed values for quantity for the quantity dropdown.
 *
 * @returns array
 *   The list of allowed values for quantity.
 */
const getQuantityDropdownValues = () => (
  drupalSettings.showQuantity
    && typeof drupalSettings.cartQuantityOptions === 'object'
    ? Object.values(drupalSettings.cartQuantityOptions)
    : []
);

/**
 * Helper function to check if max sale quantity message is enabled.
 */
const isHideMaxSaleMsg = () => {
  if (typeof drupalSettings.maxSaleHideMessage !== 'undefined') {
    return drupalSettings.maxSaleHideMessage;
  }

  return false;
};

/**
 * Helper function to check if max sale quantity condition is enabled.
 */
const isMaxSaleQtyEnabled = () => {
  if (typeof drupalSettings.maxSaleQuantityEnabled !== 'undefined') {
    return drupalSettings.maxSaleQuantityEnabled;
  }

  return false;
};

/**
 * Return true if current view is mobile otherwise false.
 */
const isMobile = () => (window.innerWidth < 768);

/**
 * Return the current view of device.
 *
 * @returns boolean
 *   If the device is desktop or not.
 */
const isDesktop = () => (
  window.innerWidth > 1023
);

export {
  isDisplayConfigurableBoxes,
  isProductBuyable,
  getHiddenFormAttributes,
  getQuantityDropdownValues,
  isHideMaxSaleMsg,
  isMaxSaleQtyEnabled,
  isDesktop,
  isMobile,
  getFilteredProductAttributes,
};
