/**
 * Helper function to check if AURA is enabled.
 * 
 * This will be true only when alshaya_aura_react module is enabled.
 */
function isAuraEnabled() {
  let enabled = false;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'enabled')
    && drupalSettings.aura.enabled) {
    enabled = true;
  }

  return enabled;
}

export {
  isAuraEnabled,
};