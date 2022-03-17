(function (drupalSettings) {
  // Initialize the global object.
  window.alshayaBazaarVoice = window.alshayaBazaarVoice || {};

  /**
   * Returns a review for the user for the current/mentioned product.
   *
   * (optional) @param {string} productIdentifier
   *   The sku value for the product.
   *
   * @returns {Object}
   *   The product review data.
   */
  window.alshayaBazaarVoice.getProductReviewForCurrrentUser = async function getProductReviewForCurrrentUser(productId) {
    if (productId !== '' && typeof drupalSettings.bazaarvoiceUserDetails !== 'undefined') {
      if (typeof productId !== 'undefined' && Object.keys(drupalSettings.productInfo[productId]).length > 0) {
        return drupalSettings.productInfo[productId].productReview;
      }
      if (drupalSettings.bazaarvoiceUserDetails.productReview !== undefined) {
        return drupalSettings.bazaarvoiceUserDetails.productReview;
      }
    }
    return null;
  }
})(drupalSettings);
