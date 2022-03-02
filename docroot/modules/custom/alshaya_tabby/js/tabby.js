(function (Drupal, drupalSettings) {
  // Function to initialize the promo widget.
  Drupal.tabbyPromoInit = function (selector, amount, source) {
    // Tabby promo change event.
    new TabbyProductPageSnippetAlShaya({
      selector: selector,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      price: amount,
      installmentsCount: drupalSettings.tabby.installmentCount,
      lang: drupalSettings.path.currentLanguage,
      source: source,
      api_key: drupalSettings.tabby.public_key
    });
  }
  // Function to initialize the info widget.
  Drupal.tabbyInfoInit = function (selector, amount) {
    new TabbyLimit5kWidgetAlShaya({
      selector: selector,
      lang: drupalSettings.path.currentLanguage,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      price: amount
    });
  }
  // Function to initialize the info widget.
  Drupal.tabbyCardInit = function (selector, amount) {
    new TabbyCard({
      selector: selector,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      lang: drupalSettings.path.currentLanguage,
      price: amount,
      size: 'wide',
      theme: 'default',
      header: false
    });
  }
  // Initialize the promo popup on page.
  Drupal.tabbyPromoPopup = function (amount) {
    new TabbyPromoAlShaya({
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      price: amount,
      infoAttrName: 'tabbyInfoAlshaya',
    });
  }
})(Drupal, drupalSettings);
