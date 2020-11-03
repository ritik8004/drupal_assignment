import { getAPIData, postAPIData } from './api/fetchApiData';
import {
  getAllAuraStatus,
  getUserDetails,
} from './helper';
import dispatchCustomEvent from '../../../js/utilities/events';
import {
  setStorageInfo,
  removeStorageInfo,
} from '../../../js/utilities/storage';
import { getAuraLocalStorageKey } from './aura_utils';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../js/utilities/showRemoveFullScreenLoader';

/**
 * Helper function to get customer details.
 */
function getCustomerDetails(tier, loyaltyStatus) {
  // API call to get customer points for logged in users.
  const apiUrl = `get/loyalty-club/get-customer-details?tier=${tier}&status=${loyaltyStatus}`;
  const apiData = getAPIData(apiUrl);
  let stateValues = {};

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        const userLoyaltyStatus = result.data.auraStatus !== undefined
          ? result.data.auraStatus : loyaltyStatus;

        stateValues = {
          loyaltyStatus: userLoyaltyStatus,
          tier: result.data.tier || tier,
          points: result.data.auraPoints || 0,
          cardNumber: result.data.cardNumber || '',
          expiringPoints: result.data.auraPointsToExpire || 0,
          expiryDate: result.data.auraPointsExpiryDate || '',
          pointsOnHold: result.data.auraOnHoldPoints || 0,
        };

        // If user's loyalty status is APC_LINKED_VERIFIED or
        // APC_LINKED_NOT_VERIFIED or APC_NOT_LINKED_DATA, then sign up is
        // complete for the user and we show points in header.
        if (userLoyaltyStatus === getAllAuraStatus().APC_LINKED_VERIFIED
          || userLoyaltyStatus === getAllAuraStatus().APC_LINKED_NOT_VERIFIED
          || userLoyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_DATA) {
          stateValues.signUpComplete = true;
        }
      }

      stateValues.wait = false;
      dispatchCustomEvent('customerDetailsFetched', { stateValues });
    });
  }
}

/**
 * Helper function to handle signup from header.
 */
function handleSignUp(auraUserDetails) {
  const auraStatus = getAllAuraStatus().APC_LINKED_NOT_VERIFIED;
  let auraUserData = {
    signUpComplete: true,
  };

  if (getUserDetails().id) {
    auraUserData.loyaltyStatus = auraStatus;
  } else if (auraUserDetails) {
    // For anonymous users, store aura data in local storage and update state.
    auraUserData = {
      signUpComplete: true,
      loyaltyStatus: auraUserDetails.data.apc_link || 0,
      points: auraUserDetails.data.apc_points || 0,
      cardNumber: auraUserDetails.data.apc_identifier_number || '',
      tier: auraUserDetails.data.tier_info || '',
      email: auraUserDetails.data.email || '',
      mobile: auraUserDetails.data.mobile || '',
    };
    setStorageInfo(auraUserData, getAuraLocalStorageKey());
  }

  dispatchCustomEvent('loyaltyStatusUpdatedFromHeader', { stateValues: auraUserData });
}

function updateUsersLoyaltyStatus(cardNumber, auraStatus, link) {
  // API call to update user's loyalty status.
  showFullScreenLoader();
  let stateValues = {};
  const apiUrl = 'post/loyalty-club/apc-status-update';
  const data = {
    uid: getUserDetails().id,
    apcIdentifierId: cardNumber,
    apcLinkStatus: auraStatus,
    link,
  };
  const apiData = postAPIData(apiUrl, data);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            loyaltyStatus: auraStatus,
            signUpComplete: false,
          };
        }
      }
      dispatchCustomEvent('loyaltyStatusUpdatedFromHeader', { stateValues });
      removeFullScreenLoader();
    });
  }
}

/**
 * Helper function to handle not you click from header.
 */
function handleNotYou(cardNumber) {
  const auraStatus = getAllAuraStatus().APC_NOT_LINKED_NOT_U;

  if (getUserDetails().id) {
    updateUsersLoyaltyStatus(cardNumber, auraStatus, 'N');
  } else {
    removeStorageInfo(getAuraLocalStorageKey());
    dispatchCustomEvent('loyaltyStatusUpdatedFromHeader', { stateValues: { loyaltyStatus: auraStatus, signUpComplete: false }, clickedNotYou: true });
  }
}

export {
  getCustomerDetails,
  handleSignUp,
  handleNotYou,
};
