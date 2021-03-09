/* eslint-disable */
/**
 * Helper function to get current product details.
 */
function getCurrentProductDetails() {
  let productDetails = {};
  if (typeof drupalSettings.productInfo !== 'undefined') {
    productDetails = drupalSettings.productInfo;
  }

  return productDetails;
}

export {
  getCurrentProductDetails,
};
