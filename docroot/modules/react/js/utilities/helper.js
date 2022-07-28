import { hasValue } from './conditionsUtility';

/**
 * Helper function to check if AURA is enabled.
 */
export default function isAuraEnabled() {
  let enabled = false;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'enabled')) {
    enabled = drupalSettings.aura.enabled;
  } else if (hasValue(drupalSettings.helloMember)
    && hasValue(drupalSettings.helloMember.status)
    && hasValue(drupalSettings.helloMember.auraIntegrationStatus)) {
    enabled = true;
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
