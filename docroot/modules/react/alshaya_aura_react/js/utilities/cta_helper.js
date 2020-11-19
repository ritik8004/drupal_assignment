import { postAPIData } from './api/fetchApiData';
import {
  getAllAuraStatus,
  getUserDetails,
} from './helper';
import dispatchCustomEvent from '../../../js/utilities/events';
import {
  setStorageInfo,
} from '../../../js/utilities/storage';
import { getAuraLocalStorageKey, getAuraDetailsDefaultState } from './aura_utils';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../js/utilities/showRemoveFullScreenLoader';

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
      loyaltyStatus: auraUserDetails.data.apc_link || auraStatus,
      points: auraUserDetails.data.apc_points || 0,
      cardNumber: auraUserDetails.data.apc_identifier_number || '',
      tier: auraUserDetails.data.tier_info || '',
      email: auraUserDetails.data.email || '',
      mobile: auraUserDetails.data.mobile || '',
    };
    setStorageInfo(auraUserData, getAuraLocalStorageKey());
  }

  dispatchCustomEvent('loyaltyStatusUpdated', { stateValues: auraUserData });
}

function updateUsersLoyaltyStatus(cardNumber, auraStatus, link) {
  // API call to update user's loyalty status.
  const apiUrl = 'post/loyalty-club/apc-status-update';
  const data = {
    uid: getUserDetails().id,
    apcIdentifierId: cardNumber,
    apcLinkStatus: auraStatus,
    link,
  };

  return postAPIData(apiUrl, data);
}

/**
 * Helper function to handle not you click from header.
 */
function handleNotYou(cardNumber) {
  let stateValues = {};
  const auraStatus = getAllAuraStatus().APC_NOT_LINKED_NOT_U;
  showFullScreenLoader();
  const apiData = updateUsersLoyaltyStatus(cardNumber, auraStatus, 'N');

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            ...getAuraDetailsDefaultState(),
            loyaltyStatus: auraStatus,
            signUpComplete: false,
          };
        }
      }
      dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
      removeFullScreenLoader();
    });
  }
}

/**
 * Helper function to handle link your card.
 */
function handleLinkYourCard(cardNumber) {
  let stateValues = {};
  const auraStatus = getAllAuraStatus().APC_LINKED_NOT_VERIFIED;
  showFullScreenLoader();
  const apiData = updateUsersLoyaltyStatus(cardNumber, auraStatus, 'Y');

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            loyaltyStatus: auraStatus,
          };
        }
      }
      dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
      removeFullScreenLoader();
    });
  }
}

/**
 * Helper function to call APC search API to search aura details
 * of a customer based on email/card/mobile.
 */
function handleSearch(data) {
  let stateValues = {};

  const apiUrl = 'post/loyalty-club/search-apc-user';
  const apiData = postAPIData(apiUrl, data);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            loyaltyStatus: result.data.data.apc_link || 0,
            points: result.data.data.apc_points || 0,
            cardNumber: result.data.data.apc_identifier_number || '',
            tier: result.data.data.tier_info || '',
            email: result.data.data.email || '',
            mobile: result.data.data.mobile || '',
          };
        }
      }
      dispatchCustomEvent('loyaltyDetailsSearchComplete', { stateValues });
      removeFullScreenLoader();
    });
  }
}

export {
  handleLinkYourCard,
  handleNotYou,
  handleSignUp,
  handleSearch,
};
