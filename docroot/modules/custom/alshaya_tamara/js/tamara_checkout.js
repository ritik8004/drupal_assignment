(function (Drupal) {
  // Function to render the tamara installment widget.
  Drupal.tamaraInitInstallmentWidget = function () {
    // Render the installment plan widget.
    if (window.TamaraInstallmentPlan) {
      window.TamaraInstallmentPlan.render();
    }
  }

  Drupal.tamaraInitInfoWidget = function () {
    // Render the installment plan widget.
    if (window.TamaraWidget) {
      window.TamaraWidget.render();
    }
  }
})(Drupal);
