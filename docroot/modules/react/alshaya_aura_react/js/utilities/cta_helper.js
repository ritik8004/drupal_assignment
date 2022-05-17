import {
  getAllAuraStatus,
  getUserDetails,
} from './helper';
import dispatchCustomEvent from '../../../js/utilities/events';
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
      loyaltyStatus: auraUserDetails.data.apc_link
        ? parseInt(auraUserDetails.data.apc_link, 10)
        : auraStatus,
      points: auraUserDetails.data.apc_points || 0,
      cardNumber: auraUserDetails.data.apc_identifier_number || '',
      tier: auraUserDetails.data.tier_info || '',
      email: auraUserDetails.data.email || '',
      mobile: auraUserDetails.data.mobile || '',
    };

    Drupal.addItemInLocalStorage(
      getAuraLocalStorageKey(),
      auraUserData,
    );
  }

  dispatchCustomEvent('loyaltyStatusUpdated', {
    showCongratulationsPopup: true,
    stateValues: auraUserData,
  });
}

function updateUsersLoyaltyStatus(cardNumber, link) {
  const data = {
    uid: getUserDetails().id,
    apcIdentifierId: cardNumber,
    link,
  };

  // API call to update user's loyalty status.
  return window.auraBackend.updateUserAuraStatus(data);
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
    Drupal.removeItemFromLocalStorage(getAuraLocalStorageKey());
    dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
    return;
  }

  // Logged in users.
  removeInlineError('.error-placeholder');
  addInlineLoader('.not-you-wrapper');
  const apiData = updateUsersLoyaltyStatus(cardNumber, 'N');

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
  let showCongratulations = true;
  removeInlineError('.error-placeholder');
  addInlineLoader('.link-card-wrapper');
  const apiData = updateUsersLoyaltyStatus(cardNumber, 'Y');

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status && result.data.data.auraStatus) {
          const {
            auraStatus,
            tier,
            auraPoints,
            auraPointsToExpire,
            auraPointsExpiryDate,
            auraOnHoldPoints,
            firstName,
            lastName,
          } = result.data.data;

          stateValues = {
            loyaltyStatus: auraStatus || 0,
            tier: tier || '',
            points: auraPoints || 0,
            cardNumber: cardNumber || '',
            expiringPoints: auraPointsToExpire || 0,
            expiryDate: auraPointsExpiryDate || '',
            pointsOnHold: auraOnHoldPoints || 0,
            firstName: firstName || '',
            lastName: lastName || '',
          };
        }
      } else {
        stateValues = {
          linkCardFailed: true,
        };
        // Set showCongratulations to false.
        // We don't want to show congratulations popup in case if linking is failed.
        showCongratulations = false;
        showInlineError('.error-placeholder', Drupal.t('Unexpected error occured.'));
      }

      // Dispatch loyaltyStatusUpdated as loyalty status is updated.
      dispatchCustomEvent('loyaltyStatusUpdated', {
        showCongratulationsPopup: showCongratulations,
        stateValues,
      });
      removeInlineLoader('.link-card-wrapper');
      removeFullScreenLoader();
    });
  }
}

/**
 * Helper function to handle manual link your card.
 */
function handleManualLinkYourCard(cardNumber, mobile, otp) {
  const data = {
    type: 'withOtp',
    uid: getUserDetails().id,
    apcIdentifierId: cardNumber,
    link: 'Y',
    phoneNumber: mobile,
    otp,
  };

  // API call to verify otp and update user's loyalty status.
  const apiData = window.auraBackend.updateUserAuraStatus(data);
  showFullScreenLoader();

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined) {
        if (result.data.status && result.data.error === undefined) {
          const {
            auraStatus,
            tier,
            auraPoints,
            auraPointsToExpire,
            auraPointsExpiryDate,
            auraOnHoldPoints,
            firstName,
            lastName,
          } = result.data.data;

          const stateValues = {
            loyaltyStatus: auraStatus || 0,
            tier: tier || '',
            points: auraPoints || 0,
            cardNumber: cardNumber || '',
            expiringPoints: auraPointsToExpire || 0,
            expiryDate: auraPointsExpiryDate || '',
            pointsOnHold: auraOnHoldPoints || 0,
            firstName: firstName || '',
            lastName: lastName || '',
          };
          dispatchCustomEvent('loyaltyStatusUpdated', {
            showCongratulationsPopup: true,
            stateValues,
          });
          removeFullScreenLoader();
          return;
        }
      }

      // Show error based on error code,
      // if error code is 500 show error message,
      // by default show invalid OTP error message.
      switch (result.data.error_code) {
        case 500:
        case '500':
          showError('otp-error', result.data.error_message);
          break;

        default:
          showError('otp-error', getStringMessage('form_error_invalid_otp'));
      }
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
