import { postAPIData } from './api/fetchApiData';
import {
  getAllAuraStatus,
  getUserDetails,
} from './helper';
import dispatchCustomEvent from '../../../js/utilities/events';
import {
  setStorageInfo,
} from '../../../js/utilities/storage';
import {
  getAuraLocalStorageKey,
  getAuraDetailsDefaultState,
  addInlineLoader,
  removeInlineLoader,
  showInlineError,
  removeInlineError,
} from './aura_utils';

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

  if (!getUserDetails().id) {
    stateValues = {
      ...getAuraDetailsDefaultState(),
      loyaltyStatus: auraStatus,
      signUpComplete: false,
    };
    dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
    return;
  }

  removeInlineError('.error-placeholder');
  addInlineLoader('.not-you-loader-placeholder');
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
      } else {
        stateValues = {
          notYouFailed: true,
        };
        showInlineError('.error-placeholder', Drupal.t('Unexpected error occured.'));
      }
      dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
      removeInlineLoader('.not-you-loader-placeholder');
    });
  }
}

/**
 * Helper function to handle link your card.
 */
function handleLinkYourCard(cardNumber) {
  let stateValues = {};
  const auraStatus = getAllAuraStatus().APC_LINKED_NOT_VERIFIED;
  removeInlineError('.error-placeholder');
  addInlineLoader('.link-card-loader-placeholder');
  const apiData = updateUsersLoyaltyStatus(cardNumber, auraStatus, 'Y');

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            loyaltyStatus: auraStatus,
          };
        }
      } else {
        stateValues = {
          linkCardFailed: true,
        };
        showInlineError('.error-placeholder', Drupal.t('Unexpected error occured.'));
      }
      dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
      removeInlineLoader('.link-card-loader-placeholder');
    });
  }
}

export {
  handleLinkYourCard,
  handleNotYou,
  handleSignUp,
};
