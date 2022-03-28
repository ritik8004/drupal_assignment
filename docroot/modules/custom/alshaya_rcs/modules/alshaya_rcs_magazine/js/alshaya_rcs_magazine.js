/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main(drupalSettings) {
  // Event listener to update the data layer object with the proper category
  // data.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.placeholder !== 'field_magazine_shop_the_story') {
      return;
    }

    let data = e.detail.result;
    let currencyConfig = drupalSettings.alshaya_spc.currency_config;

    // @todo Find a better way to reuse this in other modules that will render product teasers.
    data.forEach((item) => {
      // Add settings.
      item['lang_code'] = drupalSettings.path.currentLanguage;

      // This setting is not being used by any brand, setting default value.
      item['show_cart_form'] = 'no-cart-form';

      // Make url relative.
      // @todo Move to a function.
      let url = new URL(item.end_user_url);
      item['url'] = url.toString().substring(url.origin.length);

      // Prepare price item.
      // @todo Get from/to prices from Graphql for non-simple prods.
      item['price_details'] = {
        display_mode: 'simple',
      };
      item['price_details']['discount'] = item.price_range.maximum_price.discount;
      item['price_details']['regular_price'] = {
        value: item.price_range.maximum_price.regular_price.value,
        currency_code: currencyConfig.currency_code,
        currency_code_position: currencyConfig.currency_code_position,
        decimal_points: currencyConfig.decimal_points,
      };
      item['price_details']['final_price'] = {
        value: item.price_range.maximum_price.final_price.value,
        currency_code: currencyConfig.currency_code,
        currency_code_position: currencyConfig.currency_code_position,
        decimal_points: currencyConfig.decimal_points,
      };

      // Prepare Assets.
      item['image'] = window.commerceBackend.getTeaserImage(item);
    });
  });
})(drupalSettings);
