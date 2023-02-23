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
 * Helper function to check if Bazaar voice is available on PDP for a given SKU.
 */
export const checkBazaarVoiceAvailableForPdp = (skuItemCode) => hasValue(drupalSettings.productInfo)
  && hasValue(drupalSettings.productInfo[skuItemCode])
  && hasValue(drupalSettings.productInfo[skuItemCode].alshaya_bazaar_voice);

/**
 * Helper function to check if Checkout Tracker is enabled.
 */
export const isCheckoutTracker = () => hasValue(drupalSettings.checkoutTracker)
  && hasValue(drupalSettings.checkoutTracker.enabled);

/**
 * Checks if Bazaar voice settings are loaded.
 */
export const checkBazaarVoiceSettingsAvailable = (settings) => {
  const { reviews } = settings;
  if (hasValue(reviews)) {
    return true;
  }

  return false;
};
