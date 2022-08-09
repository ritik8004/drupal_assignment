(function orderConfirmation(Drupal, drupalSettings) {
  document.addEventListener('dataLayerContentAlter', (e) => {
    // Check if purchase success event is triggered.
    var eventData = e.detail.data();
    if (eventData.event !== 'purchaseSuccess') {
      return;
    }

    var products = eventData.ecommerce.purchase.products;
    if ((Array.isArray(products) && products.length === 0)) {
      return;
    }

    products.forEach(function(product, index) {
      var productGtmData = JSON.stringify(product);
      // We have to do synchronous call here otherwise the GTM event will be
      // pushed even before we process all the data.
      var sku = product['gtm-temp-sku'];
      var parentSku = product['gtm-temp-parentSku'];
      var productData = Drupal.alshayaSpc.getProductDataV2Synchronous(sku, parentSku);
      if (!productData) {
        return;
      }

      // Do this so that we are able to replace the placeholders.
      rcsPhReplaceEntityPh(productGtmData, 'product', productData, drupalSettings.path.currentLanguage)
        .forEach(function eachReplacement(r) {
          const fieldPh = r[0];
          const entityFieldValue = r[1];
          productGtmData = globalThis.rcsReplaceAll(productGtmData, fieldPh, entityFieldValue);
        });

      productGtmData = JSON.parse(productGtmData);
      // Delete the keys that do not need to be pushed to GTM.
      delete(productGtmData['gtm-temp-sku']);
      delete(productGtmData['gtm-temp-parentSku']);
      // Update the array with the product gtm data having the placeholders
      // replaced.
      products[index] = productGtmData;

      // Fill in the data for productStyleCode.
      if (eventData['productStyleCode'][productData.gtmAttributes.id] === undefined) {
        eventData['productStyleCode'].push(productData.gtmAttributes.id);
      }
    });
  });
})(Drupal, drupalSettings);
