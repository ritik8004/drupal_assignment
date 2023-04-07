import { hasValue } from './conditionsUtility';

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

/**
 * Get user role authenticated or anonymous.
 *
 * @returns {boolean}
 *   True if user is authenticated.
 */
export const isUserAuthenticated = () => Boolean(window.drupalSettings.userDetails.customerId);

/**
 * Helper function to get aura user details.
 */
export const getAuraUserDetails = () => {
  let loyaltyUserDetails = {};
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'userDetails')) {
    loyaltyUserDetails = drupalSettings.aura.userDetails;
  }

  return loyaltyUserDetails;
};

/**
 * Helper function to check if Bazaar voice is available on PDP for the SKU.
 */
export const checkBazaarVoiceAvailableForPdp = () => {
  if (hasValue(drupalSettings.productReviewStats)) {
    const sanitizedSku = drupalSettings.productReviewStats.productId;

    return hasValue(drupalSettings.productInfo[sanitizedSku])
      && hasValue(drupalSettings.productInfo[sanitizedSku].alshaya_bazaar_voice);
  }
  return false;
};

/**
 * Helper function to check if Checkout Tracker is enabled.
 */
export const isCheckoutTracker = () => hasValue(drupalSettings.checkoutTracker)
  && hasValue(drupalSettings.checkoutTracker.enabled);

/**
 *
 * @param {*} stringToTrim
 *  The string to trim.
 * @param {*} characterLimit
 *  Character limit for triming the above string.
 * @returns {string}
 */

export const truncate = (stringToTrim, characterLimit) => (
  stringToTrim.length > characterLimit
    // Here we reduce characterLimit by 3.
    // So that the total length of the string match the characterLimit supplied from config.
    ? `${stringToTrim.substring(0, (characterLimit - 3))}...`
    : stringToTrim
);
