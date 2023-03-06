var glegem = glegem || function () {
  (window["glegem"].q = window["glegem"].q || []).push(arguments);
};

// Initial variable to collect ge data from
// checkout step 2.
var geData = {};

glegem("OnClientEvent", function (source, data) {
  // Initialize delivery info for ga.
  var deliveryGaData = {
    'deliveryOption': 'Home Delivery',
    'deliveryCity': '',
  };

  if (source === 'ComboChanged' && data.id === 'BillingCity') {
    deliveryGaData.deliveryCity = data.value;
  }

  if (source === 'ComboChanged' && data.id === 'ShippingCity') {
    // Update delivery city if user adds alternate shipping address.
    deliveryGaData.deliveryCity = data.value;
  }

  // BillingAddressCompleted is fired first then ShippingAddressCompleted
  // is fired for either of the option i.e same as billing or shipping address
  // alternative.
  if (source === 'ShippingAddressCompleted') {
    // Collect geData and push to GA for step 3.
    Drupal.alshayaXbCheckoutGaPush(geData, 3, deliveryGaData);
  }

});

glegem("OnCheckoutStepLoaded", function (data) {
  switch (data.StepId) {
    case data.Steps.LOADED:
      // Set ge data to use in step 3.
      geData = data;
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
