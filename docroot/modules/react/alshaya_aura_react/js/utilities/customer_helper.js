/*eslint-disable */
import {
  getAllAuraStatus,
} from './helper';
import dispatchCustomEvent from '../../../js/utilities/events';
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Helper function to get customer details.
 */
function getCustomerDetails(data = {}) {
  // API call to get customer points for logged in users.
  const apiData = window.auraBackend.getCustomerDetails(data);
  let stateValues = {};

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        const userLoyaltyStatus = result.data.auraStatus;
        stateValues = {
          loyaltyStatus: userLoyaltyStatus,
          tier: hasValue(result.data.tier) ? result.data.tier : '',
          points: hasValue(result.data.auraPoints) ? result.data.auraPoints : 0,
          cardNumber: hasValue(result.data.cardNumber) ? result.data.cardNumber : '',
          expiringPoints: hasValue(result.data.auraPointsToExpire) ? result.data.auraPointsToExpire : 0,
          expiryDate: hasValue(result.data.auraPointsExpiryDate) ? result.data.auraPointsExpiryDate : '',
          pointsOnHold: hasValue(result.data.auraOnHoldPoints) ? result.data.auraOnHoldPoints : 0,
          firstName: hasValue(result.data.firstName) ? result.data.firstName : '',
          lastName: hasValue(result.data.lastName) ? result.data.lastName : '',
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
