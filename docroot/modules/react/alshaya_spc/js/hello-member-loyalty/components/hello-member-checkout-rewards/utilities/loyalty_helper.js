import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { removeFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';

/**
 * This is duplicate of aura utility in
 * aura-loyalty/components/utilities/checkout_helper.js
 * Helper function to search loyalty details based on
 * user input and add/remove the card from cart.
 */
function processCheckoutCart(data) {
  let stateValues = {};
  const value = (data.type === 'phone')
    ? data.countryCode + data.value
    : data.value;

  const apiData = window.auraBackend.updateLoyaltyCard(data.action, data.type, value);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          // For remove action.
          if (data.action !== undefined && data.action === 'remove') {
            stateValues = {
              points: 0,
              cardNumber: '',
              email: '',
              mobile: '',
            };

            dispatchCustomEvent('loyaltyCardRemovedFromCart', { stateValues });
            removeFullScreenLoader();
            return;
          }

          // For add action.
          stateValues = {
            isFullyEnrolled: result.data.data.is_fully_enrolled || false,
            points: result.data.data.apc_points || 0,
            cardNumber: result.data.data.apc_identifier_number || '',
            email: result.data.data.email || '',
          };

          if (data.type === 'phone') {
            stateValues.mobile = data.value;
            stateValues.userCountryCode = data.countryCode;
          }
        }
      } else {
        stateValues = result.data;
      }
      dispatchCustomEvent('loyaltyDetailsSearchComplete', { stateValues, searchData: data });
      removeFullScreenLoader();
    });
  }
}

/**
 * Utility function to get aura localStorage key for checkout.
 */
function getHelloMemberAuraStorageKey() {
  return 'aura_hello_member_data';
}

export {
  processCheckoutCart,
  getHelloMemberAuraStorageKey,
};
