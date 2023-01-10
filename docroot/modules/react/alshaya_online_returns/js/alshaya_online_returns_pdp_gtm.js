/**
 * @file
 * Event Listener to alter pdp gtm attributes.
 */

(($, Drupal) => {
  // Add the product returnable information in gtm product object.
  document.addEventListener('onProductDetailView', (e) => {
    const product = e.detail.data();
    const node = $(this).parents('article.entity--type-node:first');
    const sku = $(node).attr('data-sku');
    const viewMode = $(node).attr('data-vmode');
    const productKey = Drupal.getProductKeyForProductViewMode(viewMode);

    // On non-buyable PDPs, 'alshaya_acm_product.utility.js' is not getting
    // attached.
    const productInfo = (typeof window.commerceBackend.getProductData !== 'undefined')
      ? window.commerceBackend.getProductData(sku, productKey)
      : null;

    if (productInfo === null) {
      return;
    }

    // Now validate if the product information is available or not.
    if (Drupal.hasValue(product.id)
      && Drupal.hasValue(productInfo[product.id])) {
      // Load the parent product information.
      const parentProduct = productInfo[product.id];
      // Now check if variant information is available or not. If available
      // then use return eligiblity from variant else from parent.
      if (Drupal.hasValue(product.variant)
        && Drupal.hasValue(parentProduct.variants)
        && Drupal.hasValue(parentProduct.variants[product.variant])) {
        product.returnEligibility = parentProduct.variants[product.variant].eligibleForReturn ? 'yes' : 'no';
      } else {
        product.returnEligibility = parentProduct.eligibleForReturn ? 'yes' : 'no';
      }
    }
  });
})(jQuery, Drupal);
