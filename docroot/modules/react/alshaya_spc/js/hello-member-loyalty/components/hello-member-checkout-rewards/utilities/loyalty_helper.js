import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { removeFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import { getElementValueByType } from '../../../../aura-loyalty/components/utilities/link_card_sign_up_modal_helper';
import { validateElementValueByType } from '../../../../aura-loyalty/components/utilities/validation_helper';

/**
 * Utility function to get user input value.
 */
function getUserInput(linkCardOption, chosenCountryCode) {
  if (!validateElementValueByType(linkCardOption)) {
    return {};
  }

  const element = {
    key: linkCardOption,
    type: linkCardOption,
    value: getElementValueByType(linkCardOption),
  };

  if (linkCardOption === 'mobile' || linkCardOption === 'mobileCheckout') {
    element.type = 'phone';
    element.value = hasValue(chosenCountryCode)
      ? chosenCountryCode + element.value
      : element.value;
  }

  if (linkCardOption === 'emailCheckout') {
    element.key = 'email';
    element.type = 'email';
  }

  if (linkCardOption === 'cardNumber' || linkCardOption === 'cardNumberCheckout') {
    element.type = 'apcNumber';
  }

  return element;
}

/**
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
              isFullyEnrolled: false,
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
            mobile: result.data.data.mobile || '',
          };
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
  getUserInput,
  getHelloMemberAuraStorageKey,
};
