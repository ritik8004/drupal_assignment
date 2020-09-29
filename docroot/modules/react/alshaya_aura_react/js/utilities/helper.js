/**
 * Helper function to check if key exists in aura settings.
 */
function existsInAuraSettings(key) {
  let exists = false;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, key)) {
    exists = true;
  }

  return exists;
}

/**
 * Helper function to check if AURA is enabled.
 */
function isAuraEnabled() {
  let enabled = false;
  if (existsInAuraSettings('enabled') && drupalSettings.aura.enabled) {
    enabled = true;
  }

  return enabled;
}

/**
 * Helper function to get AURA Loyalty Status.
 */
function getAuraStatus() {
  let loyaltyStatus = '';
  if (existsInAuraSettings('user_details')
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
  if (existsInAuraSettings('user_details')
    && ({}).hasOwnProperty.call(drupalSettings.aura.user_details, 'tier')) {
    tier = drupalSettings.aura.user_details.tier || '';
  }

  return tier;
}

/**
 * Helper function to get all AURA Loyalty Status.
 */
function getAllAuraStatus() {
  let allLoyaltyStatus = [];
  if (existsInAuraSettings('allAuraStatus') && drupalSettings.aura.allAuraStatus.length) {
    allLoyaltyStatus = drupalSettings.aura.allAuraStatus || [];
  }

  return allLoyaltyStatus;
}

/**
 * Helper function to get all AURA Tiers.
 */
function getAllAuraTier() {
  let allTiers = [];
  if (existsInAuraSettings('allTiers') && drupalSettings.aura.allTiers.length) {
    allTiers = drupalSettings.aura.allTiers || [];
  }

  return allTiers;
}

export {
  isAuraEnabled,
  getAuraStatus,
  getAuraTier,
  getAllAuraStatus,
  getAllAuraTier,
};
