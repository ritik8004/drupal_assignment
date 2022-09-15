(function rcsOnlineReturnsproductUtility() {
  /**
   * Checks if the product is returnable.
   *
   * @param {Object} product
   *   Product object.
   *
   * @returns {Boolean}
   *   Returns true if product is returnable, else false.
   */
  function isProductReturnable(product) {
    return Drupal.hasValue(product.is_returnable)
      && !(parseInt(product.is_returnable, 10) !== 1);
  }

  document.addEventListener('rcsProductInfoAlter', function alterProduct(e) {
    e.detail.data.processedProduct.eligibleForReturn = isProductReturnable(e.detail.data.rawProduct);
  });
})();
