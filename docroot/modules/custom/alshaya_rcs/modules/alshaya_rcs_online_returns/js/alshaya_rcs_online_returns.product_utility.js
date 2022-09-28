window.commerceBackend = window.commerceBackend || {};

(function rcsOnlineReturnsproductUtility() {
  /**
   * Checks if the product is returnable.
   *
   * @param {Object} product
   *   Product object.
   * @param {String} variantSku
   *   Variant SKU value.
   *
   * @returns {Boolean}
   *   Returns true if product is returnable, else false.
   */
  window.commerceBackend.isProductReturnable = function isProductReturnable(product, variantSku) {
    var orderProduct = product;
    if (product.type_id === 'configurable') {
      product.variants.some(function eachVariant(variant) {
        if (variant.product.sku === variantSku) {
          orderProduct = variant.product;
          return true;
        }
        return false;
      });
    }

    return Drupal.hasValue(orderProduct.is_returnable)
      && !(parseInt(orderProduct.is_returnable, 10) !== 1);
  }

  document.addEventListener('rcsProductInfoAlter', function alterProduct(e) {
    e.detail.data.processedProduct.eligibleForReturn = window.commerceBackend.isProductReturnable(e.detail.data.rawProduct);
  });
})();
