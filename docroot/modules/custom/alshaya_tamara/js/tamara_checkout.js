(function ($, Drupal) {
  window.tamaraAsyncCallback = function () {
    // Initialise the Tamara widget configs.
    window.TamaraInstallmentPlan.init({
      lang: 'en',
      currency: 'AED',
      // @todo add this when available
      // publicKey: {{tamara_public_key}}
    })

    // Render the installment plan widget.
    window.TamaraInstallmentPlan.render()
  }
})(jQuery, Drupal);
