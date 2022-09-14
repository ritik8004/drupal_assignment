(function (Drupal, drupalSettings) {
  // Function to render the tamara installment widget.
  Drupal.tamaraInitInstallmentWidget = function () {
    // Initialise the Tamara widget configs.
    window.TamaraInstallmentPlan.init({
      lang: drupalSettings.path.currentLanguage,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      // @todo: need to check with public key.
      // publicKey: drupalSettings.tamara.publicKey,
    });

    // Render the installment plan widget.
    window.TamaraInstallmentPlan.render();
  }

  Drupal.tamaraInitInfoWidget = function () {
    // Initialise the Tamara widget configs.
    window.TamaraWidget.init({
      lang: drupalSettings.path.currentLanguage,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
    });

    // Render the installment plan widget.
    window.TamaraWidget.render();
  }
})(Drupal, drupalSettings);
