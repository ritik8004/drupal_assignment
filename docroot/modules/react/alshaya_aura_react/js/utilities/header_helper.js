/*eslint-disable */
import {
  getAllAuraStatus,
  getUserAuraStatus,
  getUserAuraTier,
} from './helper';
import dispatchCustomEvent from '../../../js/utilities/events';

/**
 * Helper function to get customer details.
 */
function getCustomerDetails() {
  // API call to get customer points for logged in users.
  const apiData = window.auraBackend.getCustomerDetails();
  let stateValues = {};

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        stateValues = {
          loyaltyStatus: result.data.auraStatus || getUserAuraStatus(),
          tier: result.data.tier || getUserAuraTier(),
          points: result.data.auraPoints || 0,
          cardNumber: result.data.cardNumber || '',
          expiringPoints: result.data.auraPointsToExpire || 0,
          expiryDate: result.data.auraPointsExpiryDate || '',
          pointsOnHold: result.data.auraOnHoldPoints || 0,
          firstName: result.data.firstName || '',
          lastName: result.data.lastName || '',
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

export {
  getCustomerDetails,
};
