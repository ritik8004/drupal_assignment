/**
 * Listens to the 'alshayaRcsUpdateResults' event and updated the result object.
 */
(function main(drupalSettings) {
  // Event listener to update the data layer object with the proper category
  // data.
  document.addEventListener('alshayaRcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.placeholder !== 'field_magazine_shop_the_story') {
      return;
    }

    var data = e.detail.result;

    data.forEach((item) => {
      // Add settings.
      item['lang_code'] = drupalSettings.path.currentLanguage;

      // This setting is not being used by any brand, setting default value.
      item['show_cart_form'] = 'no-cart-form';

      // Relative url.
      // @todo Move to a function.
      const url = new URL(item.end_user_url);
      item['url'] = url.toString().substring(url.origin.length);

      // Price logic CORE-34151.
      // @todo Get from/to prices from Graphql for non-simple prods.
      item['price_details'] = item.price_range.maximum_price;
      item['price_details']['display_mode'] = 'simple';

      // Assets.
      item['image'] = item.assets_teaser;
      if (item.type_id === 'configurable') {
        const assets = JSON.parse(item.variants[0].product.assets_teaser);
        item['image'] = assets[0].styles;
      }
    });
  });
})(drupalSettings);
