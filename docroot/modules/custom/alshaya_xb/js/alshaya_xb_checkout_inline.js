var glegem = glegem || function () {
  (window["glegem"].q = window["glegem"].q || []).push(arguments);
};

// Global variable to store the GE checkout data to be used in step 2.
var geData = {};

glegem("OnClientEvent", function (source, data) {
  if ((source === 'ComboChanged' || source === 'fieldBlur')) {
    geData.xbDeliveryInfo = {
      deliveryOption: 'Home Delivery',
    };

    if (data.id === 'BillingCity' || data.id === 'ShippingCity') {
      // Set delivery city.
      geData.xbDeliveryInfo.deliveryCity = data.value;
    } else if (data.id === 'BillingCityRegionID' || data.id === 'ShippingCityRegionID') {
      // Set delivery region.
      geData.xbDeliveryInfo.deliveryRegion = data.value;
    }
  }

  // BillingAddressCompleted is fired first then ShippingAddressCompleted
  // is fired for either of the option i.e same as billing or shipping address
  // alternative.
  if (source === 'ShippingAddressCompleted') {
    // Collect geData and push to GA for step 3.
    Drupal.alshayaXbCheckoutGaPush(geData, 3);
  }

});

glegem("OnCheckoutStepLoaded", function (data) {
  switch (data.StepId) {
    case data.Steps.LOADED:
      // Store GE data into the global variable that will be used in step 3.
      geData = data;
      // Push data to datalayer.
      Drupal.alshayaXbCheckoutGaPush(data, 2);
      // Trigger step 3 if address already entered by customer.
      if (Drupal.isXbAddressAvailable(data)) {
        Drupal.alshayaXbCheckoutGaPush(data, 3);
      }

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
