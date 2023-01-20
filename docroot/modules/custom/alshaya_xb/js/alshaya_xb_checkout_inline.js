var glegem = glegem || function () {
  (window["glegem"].q = window["glegem"].q || []).push(arguments);
};

glegem("OnClientEvent", function (source, data) {
  console.log(GEMerchantUtils.ClientEvents.BILLING_DETAILS_COMPLETED);
  console.log(source, data);
});

glegem("OnCheckoutStepLoaded", function (data) {
  switch (data.StepId) {
    case data.Steps.LOADED:
      // Push data to datalayer.
      Drupal.alshayaXbCheckoutGaPush(data, 2);
      break;

    case data.Steps.CONFIRMATION:
      // Push data to datalayer.
      Drupal.alshayaXbCheckoutGaPush(data, 4);
      // Clear local storage.
      window.commerceBackend.removeCartDataFromStorage(true);
      Drupal.removeItemFromLocalStorage('ge_cart_id');
      break;
  }
});
