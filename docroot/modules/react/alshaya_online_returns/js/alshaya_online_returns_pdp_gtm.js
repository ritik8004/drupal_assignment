/**
 * @file
 * Event Listener to alter pdp gtm attributes.
 */

((Drupal, drupalSettings) => {
  // Add the product returnable information in gtm product object.
  document.addEventListener('onProductDetailView', (e) => {
    const product = e.detail.data();
    // Now validate if the product information is available or not.
    if (Drupal.hasValue(drupalSettings.productInfo)
      && Drupal.hasValue(product.id)
      && Drupal.hasValue(drupalSettings.productInfo[product.id])) {
      // Load the parent product information.
      const parentProduct = drupalSettings.productInfo[product.id];
      // Now check if variant information is available or not. If available
      // then use return eligiblity from variant else from parent.
      if (Drupal.hasValue(product.variant)
        && Drupal.hasValue(parentProduct.variants[product.variant])) {
        product.returnEligibility = parentProduct.variants[product.variant].eligibleForReturn ? 'yes' : 'no';
      } else {
        product.returnEligibility = parentProduct.eligibleForReturn ? 'yes' : 'no';
      }
    }
  });
})(Drupal, drupalSettings);
