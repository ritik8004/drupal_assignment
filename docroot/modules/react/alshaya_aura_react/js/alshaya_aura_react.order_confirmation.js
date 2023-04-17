(function auraOrderConfirmation(Drupal) {
  document.addEventListener('dataLayerContentAlter', (e) => {
    // Check if purchase success event is triggered.
    const eventData = e.detail.data();
    if (eventData.event === 'purchaseSuccess') {
      const gtmAuraCommonData = Drupal.getItemFromLocalStorage('gtm_aura_common_data');
      const gtmAuraCheckoutData = Drupal.getItemFromLocalStorage('gtm_aura_checkout_data');
      eventData.aura_Status = gtmAuraCommonData ? gtmAuraCommonData.aura_Status : null;
      eventData.aura_enrollmentStatus = gtmAuraCommonData
        ? gtmAuraCommonData.aura_enrollmentStatus : null;
      eventData.aura_balPointsVSorderValue = gtmAuraCheckoutData || null;
    }
  });
}(Drupal));
