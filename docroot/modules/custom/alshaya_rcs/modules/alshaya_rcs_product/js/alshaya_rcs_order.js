(function main(Drupal, RcsEventManager) {
  RcsEventManager.addListener('invokingApi', function invokingApi(e) {
    // For the order teaser section, add the promises of requests to get product
    // data to the promises array so that these are resolved before we render
    // the section.
    if (e.placeholder === 'order_teaser'
      && Drupal.hasValue(e.params)
      && Drupal.hasValue(e.params['parent-skus'])
      && Drupal.hasValue(e.params['item-skus'])
    ) {
      // Get the product data based on sku.
      var parentSkus = JSON.parse(e.params['parent-skus']);
      var itemSkus = JSON.parse(e.params['item-skus']);
      parentSkus.map(function eachSku(sku, key) {
        e.promises = e.promises.concat(Drupal.alshayaSpc.getProductDataV2(itemSkus[key], sku));
      });
    }
  });
})(Drupal, RcsEventManager);
