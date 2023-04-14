(function auraOrderConfirmation(Drupal) {
  document.addEventListener('dataLayerContentAlter', (e) => {
    // Check if purchase success event is triggered.
    const eventData = e.detail.data();
    if (eventData.event === 'purchaseSuccess') {
      eventData.aura_Status = Drupal.getItemFromLocalStorage('gtm_aura_common_data') ? Drupal.getItemFromLocalStorage('gtm_aura_common_data').aura_Status : null;
      eventData.aura_enrollmentStatus = Drupal.getItemFromLocalStorage('gtm_aura_common_data') ? Drupal.getItemFromLocalStorage('gtm_aura_common_data').aura_enrollmentStatus : null;
      eventData.aura_balPointsVSorderValue = Drupal.getItemFromLocalStorage('gtm_aura_checkout_data') ? Drupal.getItemFromLocalStorage('gtm_aura_checkout_data') : null;
    }
  });
}(Drupal));
