/**
 * @file
 * Free gift cart functionalities for V3.
 */

window.commerceBackend = window.commerceBackend || {};

(function alshayaRcsFreeGiftCartUtil($, Drupal, drupalSettings) {

  /**
   * Process free gift configurable values when adding to cart.
   */
  window.commerceBackend.processFreeGiftConfigurables = async function processFreeGiftConfigurables(
    freeGiftMainSku,
    form,
  ) {
    // On Page load or opening modal, some free gift data is already cached.
    var freeGiftItem = await window.commerceBackend.fetchValidFreeGift(freeGiftMainSku);
    var configurableValues = [];
    if (Drupal.hasValue(freeGiftItem.configurable_options)) {
      freeGiftItem.configurable_options.forEach(function processEachOption(singleOption) {
        var option = {};
        if (Drupal.hasValue(singleOption.attribute_uid)) {
          var optionId = atob(singleOption.attribute_uid);
          // Skipping the psudo attributes.
          if (
            !Drupal.hasValue(drupalSettings.psudo_attribute)
            || drupalSettings.psudo_attribute === optionId
          ) {
            return;
          }
          option.option_id = optionId;
        }
        else {
          return;
        }
        option.option_value = parseInt(form.querySelector(`[data-configurable-code="${singleOption.attribute_code}"]`).value, 10);
        configurableValues.push(option);
      });
    }
    return configurableValues;
  };

})(jQuery, Drupal, drupalSettings);
