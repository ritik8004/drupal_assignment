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

    let data = e.detail.result;
    let currencyConfig = drupalSettings.alshaya_spc.currency_config;

    data.forEach((item) => {
      // Add settings.
      item['lang_code'] = drupalSettings.path.currentLanguage;

      // This setting is not being used by any brand, setting default value.
      item['show_cart_form'] = 'no-cart-form';

      // Relative url.
      // @todo Move to a function.
      let url = new URL(item.end_user_url);
      item['url'] = url.toString().substring(url.origin.length);

      // Price logic CORE-34151.
      // @todo Get from/to prices from Graphql for non-simple prods.
      item['price_details'] = item.price_range.minimum_price;
      item['price_details']['display_mode'] = 'simple';
      item['price_details']['regular_price']['currency_code'] = currencyConfig.currency_code;
      item['price_details']['regular_price']['currency_code_position'] = currencyConfig.currency_code_position;
      item['price_details']['regular_price']['decimal_points'] = currencyConfig.decimal_points;
      item['price_details']['final_price']['currency_code'] = currencyConfig.currency_code;
      item['price_details']['final_price']['currency_code_position'] = currencyConfig.currency_code_position;
      item['price_details']['final_price']['decimal_points'] = currencyConfig.decimal_points;

      // Assets.
      item['image'] = item.assets_teaser;
      if (item.type_id === 'configurable') {
        let assets = JSON.parse(item.variants[0].product.assets_teaser);
        item['image'] = assets[0].styles;
      }
    });
  });
})(drupalSettings);
