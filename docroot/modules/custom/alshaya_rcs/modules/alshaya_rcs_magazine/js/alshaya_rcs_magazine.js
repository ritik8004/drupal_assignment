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
      // Product url.
      item['url'] = `/${item.lang_code}/${item.url_key}.html`;

      // Assets.
      if (item.type_id === 'configurable') {
        const assets = JSON.parse(item.variants[0].product.assets_teaser);
        item['image'] = assets[0].styles;
      } else {
        item['image'] = item.assets_teaser;
      }
    });
  });
})(drupalSettings);
