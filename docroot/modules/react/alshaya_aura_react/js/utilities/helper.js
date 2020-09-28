/**
 * Helper function to get AURA Loyalty Status.
 */
function getAuraStatus() {
  let loyaltyStatus = '';
  if (typeof drupalSettings.aura !== 'undefined'
    && typeof drupalSettings.aura.user_details !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura.user_details, 'loyaltyStatus')) {
    loyaltyStatus = drupalSettings.aura.user_details.loyaltyStatus || '';
  }

  return loyaltyStatus;
}

/**
 * Helper function to get AURA tier.
 */
function getAuraTier() {
  let tier = '';
  if (typeof drupalSettings.aura !== 'undefined'
    && typeof drupalSettings.aura.user_details !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura.user_details, 'tier')) {
    tier = drupalSettings.aura.user_details.tier || '';
  }

  return tier;
}

export {
  getAuraStatus,
  getAuraTier,
};
