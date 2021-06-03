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

export {
  isDisplayConfigurableBoxes,
  isProductBuyable,
};
