/**
 * Helper function to check if AURA is enabled.
 */
export default function isAuraEnabled() {
  let enabled = false;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'enabled')) {
    enabled = drupalSettings.aura.enabled;
  }

  return enabled;
}
