/**
 * @file push initial data to data layer.
 */

(function auraGtmDataPush($) {
  Drupal.behaviors.dataPushAura = {
    attach() {
      $('body').once('dataPushAura').each(() => {
        drupalSettings.dataLayerContent.aura_Status = Drupal.getItemFromLocalStorage('gtm_aura_common_data') ? Drupal.getItemFromLocalStorage('gtm_aura_common_data').aura_Status : null;
        drupalSettings.dataLayerContent.aura_enrollmentStatus = Drupal.getItemFromLocalStorage('gtm_aura_common_data') ? Drupal.getItemFromLocalStorage('gtm_aura_common_data').aura_enrollmentStatus : null;
        drupalSettings.dataLayerContent.aura_balPointsVSorderValue = Drupal.getItemFromLocalStorage('gtm_aura_checkout_data') ? Drupal.getItemFromLocalStorage('gtm_aura_checkout_data') : null;
        const { dataLayerContent } = drupalSettings;
        const event = new CustomEvent('dataLayerContentAlter', {
          detail: {
            data: () => dataLayerContent,
          },
        });
        document.dispatchEvent(event);
        window.dataLayer.push(dataLayerContent);
      });
    },
  };
}(jQuery));
