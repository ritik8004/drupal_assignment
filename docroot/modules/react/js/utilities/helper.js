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
 * Helper function to get user's email id.
 */
export const getUserDetails = () => {
  let userDetails = '';
  if (hasValue(drupalSettings)
    && hasValue(drupalSettings.userDetails)) {
    userDetails = drupalSettings.userDetails;
  }
  return userDetails;
};
