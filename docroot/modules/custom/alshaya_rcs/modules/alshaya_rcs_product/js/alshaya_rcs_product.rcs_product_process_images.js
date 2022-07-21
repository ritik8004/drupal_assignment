/**
 * Listens to the 'rcsUpdateResults' event and updates the result object
 * with assets data.
 */
 (function (RcsEventManager) {

  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // We do not want further processing:
    // 1. If page is not a product page
    // 2. If the result object in the event data is undefined
    // 3. If product recommendation replacement or magazine carousel replacement
    // has not called this handler.
    if ((typeof e.detail.pageType !== 'undefined' && e.detail.pageType !== 'product')
      || typeof e.detail.result === 'undefined'
      || (typeof e.detail.placeholder !=='undefined'
           && !([
              'product_by_sku',
              'product-recommendation',
              'crosssel-products',
              'upsell-products',
              'related-products',
              'field_magazine_shop_the_story',
            ].includes(e.detail.placeholder))
         )
      ) {
      return;
    }

    var products = e.detail.result;

    switch (e.detail.placeholder || '') {
      case 'crosssel-products':
        products = products[0].crosssell_products;
        break;

      case 'upsell-products':
        products = products[0].upsell_products;
        break;

      case 'related-products':
        products = products[0].related_products;
        break;
    }

    window.commerceBackend.setMediaData(products);
  }, 10);
})(RcsEventManager);
