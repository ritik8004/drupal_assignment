/**
 * @file
 * Free gift cart functionalities for V2.
 */

window.commerceBackend = window.commerceBackend || {};

(function freeGiftCart($, Drupal, drupalSettings) {
  /**
   * Process free gift configurable values when adding to cart.
   */
  window.commerceBackend.processFreeGiftConfigurables = function processFreeGiftConfigurables(
    freeGiftMainSku,
    form,
  ) {
    var configurableValues = [];
    if (Drupal.hasValue(drupalSettings.configurableCombinations)
      && Drupal.hasValue(drupalSettings.configurableCombinations[freeGiftMainSku])
    ) {
      Object.keys(
        drupalSettings.configurableCombinations[freeGiftMainSku].configurables,
      ).forEach((key) => {
        var optionId = drupalSettings.configurableCombinations[freeGiftMainSku]
          .configurables[key].attribute_id;
        // Skipping the psudo attributes.
        if (
          !Drupal.hasValue(drupalSettings.psudo_attribute)
          || drupalSettings.psudo_attribute === optionId
        ) {
          return;
        }
        var option = {
          option_id: optionId,
          option_value: parseInt(form.querySelector(`[data-configurable-code="${key}"]`).value, 10),
        };
        configurableValues.push(option);
      });
    }
    return configurableValues;
  };

  /**
   * Utility function to start free gift modal processing and show.
   */
  window.commerceBackend.startFreeGiftModalProcess = function startFreeGiftModalProcess(sku) {
    document.getElementById(getCartFreeGiftModalId(sku)).click();
  };
}(jQuery, Drupal, drupalSettings));
