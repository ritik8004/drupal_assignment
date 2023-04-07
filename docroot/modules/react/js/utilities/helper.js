import React from 'react';
import EllipsisText from 'react-ellipsis-text';
import { hasValue } from './conditionsUtility';
import { isDesktop } from './display';

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


export const TrimString = (props) => {
  const {
    stringToTrim,
    characterLimit,
  } = props;

  if (!hasValue(stringToTrim)) {
    return null;
  }

  return (
    <div title={stringToTrim}>
      {hasValue(characterLimit) && isDesktop() ? (
        <EllipsisText
          text={stringToTrim}
          length={parseInt(characterLimit, 10)}
        />
      ) : (<>{stringToTrim}</>)}
    </div>
  );
};
