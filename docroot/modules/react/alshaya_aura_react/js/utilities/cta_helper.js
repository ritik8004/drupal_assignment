import { postAPIData } from './api/fetchApiData';
import {
  getAllAuraStatus,
  getUserDetails,
} from './helper';
import dispatchCustomEvent from '../../../js/utilities/events';
import {
  setStorageInfo,
  removeStorageInfo,
} from '../../../js/utilities/storage';
import {
  getAuraLocalStorageKey,
  getAuraDetailsDefaultState,
  addInlineLoader,
  removeInlineLoader,
  showInlineError,
  removeInlineError,
  showError,
} from './aura_utils';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../js/utilities/strings';

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

  // Guest users.
  if (!getUserDetails().id) {
    stateValues = {
      ...getAuraDetailsDefaultState(),
      loyaltyStatus: auraStatus,
      signUpComplete: false,
    };
    removeStorageInfo(getAuraLocalStorageKey());
    dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
    return;
  }

  // Logged in users.
  removeInlineError('.error-placeholder');
  addInlineLoader('.not-you-wrapper');
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
      removeInlineLoader('.not-you-wrapper');
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
  addInlineLoader('.link-card-wrapper');
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
      removeInlineLoader('.link-card-wrapper');
    });
  }
}

/**
 * Helper function to handle manual link your card.
 */
function handleManualLinkYourCard(cardNumber, mobile, otp) {
  const auraStatus = getAllAuraStatus().APC_LINKED_NOT_VERIFIED;
  const data = {
    type: 'withOtp',
    uid: getUserDetails().id,
    apcIdentifierId: cardNumber,
    apcLinkStatus: auraStatus,
    link: 'Y',
    phoneNumber: mobile,
    otp,
  };

  // API call to verify otp and update user's loyalty status.
  const apiUrl = 'post/loyalty-club/apc-status-update';
  const apiData = postAPIData(apiUrl, data);
  showFullScreenLoader();

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined) {
        if (result.data.status && result.data.error === undefined) {
          dispatchCustomEvent('loyaltyStatusUpdated', { stateValues: { loyaltyStatus: auraStatus } });
          removeFullScreenLoader();
          return;
        }
      }
      showError('otp-error', getStringMessage('form_error_invalid_otp'));
      removeFullScreenLoader();
    });
  }
}

export {
  handleLinkYourCard,
  handleNotYou,
  handleSignUp,
  handleManualLinkYourCard,
};
