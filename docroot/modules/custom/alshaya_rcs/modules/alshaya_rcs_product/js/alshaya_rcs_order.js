(function main(Drupal, RcsEventManager) {
  RcsEventManager.addListener('invokingApi', function invokingApi(e) {
    // For the order teaser section, add the promises of requests to get product
    // data to the promises array so that these are resolved before we render
    // the section.
    if (e.extraData.placeholder === 'order_teaser'
      && Drupal.hasValue(e.extraData.params)
      && Drupal.hasValue(e.extraData.params['skus'])
    ) {
      // Get the product data based on sku.
      var skus = JSON.parse(e.extraData.params['skus']);
      Object.entries(skus).forEach(function eachSku([child, parent]) {
        e.promises.push(Drupal.alshayaSpc.getProductDataV2(child, parent));
      });
    }
  });
})(Drupal, RcsEventManager);
