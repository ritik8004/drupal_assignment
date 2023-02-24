import { getAuraUserDetails } from '../../../js/utilities/helper';

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
function getAllAuraTier(value) {
  let allAuraTier = {};
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'allAuraTier')) {
    allAuraTier = drupalSettings.aura.allAuraTier[value] || {};
  }

  return allAuraTier;
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
function getUserProfileInfo(firstName, lastName) {
  const userInfo = {};
  if (firstName && firstName.length > 0) {
    userInfo.profileName = `${firstName} ${lastName}`;
    userInfo.avatar = `${firstName.charAt(0)}${lastName ? lastName.charAt(0) : ''}`;
  }

  return userInfo;
}

/**
 * Helper function to get aura user details.
 */
function getUserDetails() {
  return getAuraUserDetails();
}

/**
 * Helper function to get aura config.
 */
function getAuraConfig() {
  let loyaltyConfig = {};
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'config')) {
    loyaltyConfig = drupalSettings.aura.config;
  }

  return loyaltyConfig;
}

/**
 * Helper function to get point to price ratio.
 */
function getPointToPriceRatio() {
  let pointToPriceRatio = 0;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'pointToPriceRatio')) {
    pointToPriceRatio = parseInt(drupalSettings.aura.pointToPriceRatio.toString(), 10);
  }

  return pointToPriceRatio;
}

/**
 * Helper function to get price to point ratio.
 */
function getPriceToPointRatio() {
  let priceToPointRatio = 0;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'priceToPointRatio')) {
    priceToPointRatio = parseInt(drupalSettings.aura.priceToPointRatio.toString(), 10);
  }

  return priceToPointRatio;
}

/**
 * Helper function to get recognition accrual ratio.
 */
function getRecognitionAccrualRatio() {
  let recognitionAccrualRatio = 0;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'recognitionAccrualRatio')) {
    recognitionAccrualRatio = parseInt(drupalSettings.aura.recognitionAccrualRatio.toString(), 10);
  }

  return recognitionAccrualRatio;
}

export {
  getAllAuraStatus,
  getAllAuraTier,
  getLoyaltyBenefitsTitle,
  getLoyaltyBenefitsContent,
  getUserProfileInfo,
  getUserDetails,
  getAuraConfig,
  getPointToPriceRatio,
  getPriceToPointRatio,
  getRecognitionAccrualRatio,
};
