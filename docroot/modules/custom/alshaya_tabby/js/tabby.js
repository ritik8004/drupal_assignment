(function ($, Drupal, drupalSettings) {
  Drupal.tabbyPromoInit = function (selector, amount, source) {
    // Tabby promo change event.
    new TabbyPromo({
      selector: selector,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      price: amount,
      installmentsCount: drupalSettings.tabby.tabby_installment_count,
      lang: drupalSettings.tabby.locale,
      source: source,
      api_key: drupalSettings.tabby.public_key
    });
  }
})(jQuery, Drupal, drupalSettings);
