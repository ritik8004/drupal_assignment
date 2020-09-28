/**
 * Helper function to get AURA Loyalty Status.
 */
function getAuraStatus() {
  const status = drupalSettings.aura.user_details.loyaltyStatus || '';
  return status;
}

/**
 * Helper function to get AURA tier.
 */
function getAuraTier() {
  const tier = drupalSettings.aura.user_details.tier || '';
  return tier;
}

export {
  getAuraStatus,
  getAuraTier,
};
