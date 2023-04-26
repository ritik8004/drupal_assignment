(function auraOrderConfirmation(Drupal) {
  document.addEventListener('dataLayerContentAlter', (e) => {
    // Check if purchase success event is triggered.
    const eventData = e.detail.data();
    if (eventData.event === 'purchaseSuccess') {
      const gtmAuraCommonData = Drupal.getItemFromLocalStorage('gtm_aura_common_data');
      const gtmAuraBalpointsVsOrder = Drupal.getItemFromLocalStorage('gtm_aura_balpoints_vs_order');
      eventData.aura_Status = Drupal.hasValue(gtmAuraCommonData)
        ? gtmAuraCommonData.aura_Status : null;
      eventData.aura_balStatus = Drupal.hasValue(gtmAuraCommonData)
        ? gtmAuraCommonData.aura_balStatus : null;
      eventData.aura_enrollmentStatus = Drupal.hasValue(gtmAuraCommonData)
        ? gtmAuraCommonData.aura_enrollmentStatus : null;
      if (Drupal.hasValue(gtmAuraBalpointsVsOrder)) {
        eventData.aura_balPointsVSorderValue = gtmAuraBalpointsVsOrder || null;
        Drupal.removeItemFromLocalStorage('gtm_aura_balpoints_vs_order');
      }
    }
  });
}(Drupal));
