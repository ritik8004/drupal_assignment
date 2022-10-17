/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main(Drupal) {
  // Event listener to update the url key with the proper super cateogory url.

  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined') {
      return;
    }

    var result = e.detail.result;

    switch (e.detail.placeholder || '') {
      case 'crosssel-products':
        result = result[0].crosssell_products;
        break;

      case 'upsell-products':
        result = result[0].upsell_products;
        break;

      case 'related-products':
        result = result[0].related_products;
        break;
    }

    // If the result is array then process all the items.
    if (Array.isArray(result)) {
      result.forEach((item, key) => {
        if (Drupal.hasValue(item.end_user_url)) {
          result[key].url_key = item.end_user_url.replace('.html', '');
        }
      });
    } else if (Drupal.hasValue(result.end_user_url)) {
      result.url_key = result.end_user_url.replace('.html', '');
    }
  });
})(Drupal);
