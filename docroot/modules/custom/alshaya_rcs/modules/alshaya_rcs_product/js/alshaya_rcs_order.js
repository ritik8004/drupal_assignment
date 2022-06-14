(function main(Drupal, RcsEventManager) {
  RcsEventManager.addListener('invokingApi', function invokingApi(e) {
    // For the order teaser section, add the promises of requests to get product
    // data to the promises array so that these are resolved before we render
    // the section.
    if (e.extraData.placeholder === 'order_teaser'
      && Drupal.hasValue(e.extraData.params)
      && Drupal.hasValue(e.extraData.params['parent-skus'])
      && Drupal.hasValue(e.extraData.params['item-skus'])
    ) {
      // Get the product data based on sku.
      var parentSkus = JSON.parse(e.extraData.params['parent-skus']);
      var itemSkus = JSON.parse(e.extraData.params['item-skus']);
      parentSkus.map(function eachSku(sku, key) {
        e.promises.push(Drupal.alshayaSpc.getProductDataV2(itemSkus[key], sku));
      });
    }
  });
})(Drupal, RcsEventManager);
