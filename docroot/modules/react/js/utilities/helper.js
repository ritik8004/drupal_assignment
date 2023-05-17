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
 * Checks if configurable filters is enabled or disabled.
 *
 * Once algolia facet display is configured then config
 * algolia_enable_configurable_filter is set true.
 * This is passed in drupalsettings to check if facet display is configured
 * on algolia and display facet from algolia.
 *
 * @returns {boolean}
 *   True if configurable filter enabled else false.
 */
export const isConfigurableFiltersEnabled = () => {
  if (hasValue(drupalSettings.algoliaSearch)
    && hasValue(drupalSettings.algoliaSearch.enableConfigurableFilters)) {
    return drupalSettings.algoliaSearch.enableConfigurableFilters;
  }

  return false;
};
