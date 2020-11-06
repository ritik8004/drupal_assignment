/**
 * Helper function to check if AURA is enabled.
 *
 * This will be true only when alshaya_aura_react module is enabled.
 */
export function isAuraEnabled() {
  let enabled = false;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'enabled')
    && drupalSettings.aura.enabled) {
    enabled = true;
  }

  return enabled;
}

/**
 * Helper function to get product price from drupalSettings.
 */
export function getProductPrice(productKey, parentSku, variantSku) {
  let price = '';

  if (typeof drupalSettings[productKey] !== 'undefined'
    && typeof drupalSettings[productKey][parentSku] !== 'undefined'
    && typeof drupalSettings[productKey][parentSku].variants !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings[productKey][parentSku].variants, variantSku)) {
    price = drupalSettings[productKey][parentSku].variants[variantSku].priceRaw || '';
  }

  return price;
}
