/**
 * Helper function to get user's AURA Status.
 */
function getUserAuraStatus() {
  let loyaltyStatus = '';
  if (typeof drupalSettings.aura !== 'undefined'
    && typeof drupalSettings.aura.user_details !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura.user_details, 'loyaltyStatus')) {
    loyaltyStatus = drupalSettings.aura.user_details.loyaltyStatus || '';
  }

  return loyaltyStatus;
}

/**
 * Helper function to get user's AURA tier.
 */
function getUserAuraTier() {
  let tier = '';
  if (typeof drupalSettings.aura !== 'undefined'
    && typeof drupalSettings.aura.user_details !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura.user_details, 'tier')) {
    tier = drupalSettings.aura.user_details.tier || '';
  }

  return tier;
}

/**
 * Helper function to get all AURA Status.
 */
function getAllAuraStatus() {
  let allAuraStatus = {};
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'allAuraStatus')) {
    allAuraStatus = drupalSettings.aura.allAuraStatus || {};
  }

  return allAuraStatus;
}

/**
 * Helper function to get all AURA Tiers.
 */
function getAllAuraTier() {
  let allAuraTier = {};
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'allAuraTier')) {
    allAuraTier = drupalSettings.aura.allAuraTier || {};
  }

  return allAuraTier;
}

/**
 * Helper function to get user's AURA tier label.
 */
function getUserAuraTierLabel(tierValue) {
  const tierLabels = {
    1: Drupal.t('Hello'),
    2: Drupal.t('Star'),
    3: Drupal.t('VIP'),
  };

  return tierLabels[tierValue] || '';
}

/**
 * Helper function to get loyalty benefits content.
 */
function getLoyaltyBenefitsTitle() {
  let loyaltyBenefitsTitle = {
    title1: '',
    title2: '',
  };

  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'loyaltyBenefitsTitle')) {
    loyaltyBenefitsTitle = drupalSettings.aura.loyaltyBenefitsTitle;
  }

  return loyaltyBenefitsTitle;
}

/**
 * Helper function to get loyalty benefits content.
 */
function getLoyaltyBenefitsContent() {
  let loyaltyBenefitsContent = '';
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'loyaltyBenefitsContent')) {
    loyaltyBenefitsContent = drupalSettings.aura.loyaltyBenefitsContent;
  }

  return loyaltyBenefitsContent;
}

/**
 * Get User Profile info.
 */
function getUserProfileInfo() {
  const { userName } = drupalSettings.userDetails;
  const userInfo = {};
  if (userName.length > 0) {
    const parts = userName.split(' ');
    userInfo.profileName = userName;
    userInfo.avatar = `${parts[0].charAt(0)}${parts[1].charAt(0)}`;
  }

  return userInfo;
}

export {
  getUserAuraStatus,
  getUserAuraTier,
  getAllAuraStatus,
  getAllAuraTier,
  getUserAuraTierLabel,
  getLoyaltyBenefitsTitle,
  getLoyaltyBenefitsContent,
  getUserProfileInfo,
};
