// Link between RCS errors and Datadog.
(function main(Drupal) {
  // Event listener to update the the order listing and order detail results.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Only when placeholder is order_teaser.
    if (Drupal.hasValue(e.detail.placeholder)
      && e.detail.placeholder === 'order_teaser'
      && e.detail.params) {
      // Extract parent skus and item skus.
      const params = e.detail.params;
      const result = [];
      if (Drupal.hasValue(params['parent-skus'])
        && Drupal.hasValue(params['item-skus'])) {
        // Get the product data based on sku.
        const parentSkus = JSON.parse(params['parent-skus']);
        const itemSkus = JSON.parse(params['item-skus']);

        parentSkus.forEach((sku, key) => {
          result[itemSkus[key]] = Drupal.alshayaSpc.getProductDataV2Synchronous(itemSkus[key], sku);
        });
      }
      e.detail.result = result;
    }
  });
})(Drupal);
