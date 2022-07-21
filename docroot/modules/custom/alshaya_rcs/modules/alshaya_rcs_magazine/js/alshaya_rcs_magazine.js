/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main(Drupal, drupalSettings, RcsEventManager) {
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
      item['price_details'] = window.commerceBackend.getPriceForRender(item);

      // Prepare Assets.
      item['image'] = window.commerceBackend.getTeaserImage(item);
      // Clean sku value to be used in CSS class.
      item['cleanSku'] = Drupal.cleanCssIdentifier(item.sku);
    });
  });
})(Drupal, drupalSettings, RcsEventManager);
