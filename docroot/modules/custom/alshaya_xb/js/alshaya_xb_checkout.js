/**
 * @file
 * Checkout page JS implementations.
 */

(function (Drupal) {
  window.glegem("OnCheckoutStepLoaded", function checkout(data) {
    if (data.StepId === data.Steps.CONFIRMATION) {
      // Clear local storage.
      window.commerceBackend.removeCartDataFromStorage(true);
    }
  });

})(Drupal);
