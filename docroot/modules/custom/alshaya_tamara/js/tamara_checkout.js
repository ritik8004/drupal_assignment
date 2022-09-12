(function (Drupal, drupalSettings) {
  // Function to render the tamara installment widget.
  Drupal.tamaraCardInit = function () {
    // Initialise the Tamara widget configs.
    window.TamaraInstallmentPlan.init({
      lang: drupalSettings.path.currentLanguage,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      publicKey: drupalSettings.tamara.publicKey,
    });

    // Render the installment plan widget.
    window.TamaraInstallmentPlan.render();
  }
})(Drupal, drupalSettings);
