import React from 'react';
import {
  getPriceToPoint,
  getAuraDetailsDefaultState,
} from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import {
  removeFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import PointsString from './points-string';
import { getElementValueByType } from './link_card_sign_up_modal_helper';
import { validateElementValueByType } from './validation_helper';
import getStringMessage from '../../../../../js/utilities/strings';
import { getAuraConfig } from '../../../../../alshaya_aura_react/js/utilities/helper';

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
    element.value = chosenCountryCode + element.value;
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

  const apiData = window.auraBackend.updateLoyaltyCard(data.action, data.type, data.value);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          // For remove action.
          if (data.action !== undefined && data.action === 'remove') {
            stateValues = {
              ...getAuraDetailsDefaultState(),
            };

            dispatchCustomEvent('loyaltyCardRemovedFromCart', { stateValues });
            removeFullScreenLoader();
            return;
          }

          // For add action.
          let mobile;
          let userCountryCode = '';

          if (result.data.data.mobile) {
            // @todo Update code to also support countries not having exactly 3 digit country code.
            const mobileWithoutPrefixPlus = result.data.data.mobile.replace('+', '');
            mobile = mobileWithoutPrefixPlus.substring(3);
            userCountryCode = mobileWithoutPrefixPlus.substring(0, 3);
          }

          stateValues = {
            loyaltyStatus: result.data.data.apc_link || 0,
            points: result.data.data.apc_points || 0,
            cardNumber: result.data.data.apc_identifier_number || '',
            tier: result.data.data.tier_code || '',
            email: result.data.data.email || '',
            mobile,
            userCountryCode,
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
 * Utility function points to earn message.
 */
function getMembersToEarnMessage(price) {
  const toEarnMessageP1 = `${getStringMessage('checkout_members_will_earn')} `;
  const toEarnMessageP2 = ` ${getStringMessage('checkout_with_this_purchase')}`;
  const points = getPriceToPoint(price);

  return (
    <span className="spc-checkout-aura-points-to-earn">
      { toEarnMessageP1 }
      <PointsString points={points} />
      { toEarnMessageP2 }
    </span>
  );
}

/**
 * Helper function to redeem points.
 */
function redeemAuraPoints(data) {
  let stateValues = {};

  const apiData = window.auraBackend.processRedemption(data);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      removeFullScreenLoader();
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            balancePayable: result.data.data.balancePayable,
            paidWithAura: result.data.data.paidWithAura,
            balancePoints: result.data.data.balancePoints,
          };
        }
      } else {
        stateValues = result.data || { error: true };
      }
      dispatchCustomEvent('auraRedeemPointsApiInvoked', { stateValues, action: data.action });
    });
  }
}

/**
 * Utility function to check if `aura_payment` is set in cart.
 */
function isPaymentMethodSetAsAura(cart) {
  if (cart.cart.totals !== undefined
    && Object.entries(cart.cart.totals).length !== 0
    && cart.cart.totals.paidWithAura !== 0
    && cart.cart.totals.balancePayable <= 0
    && cart.cart.payment.method === 'aura_payment') {
    return true;
  }

  return false;
}

/**
 * Utility function to check if full payment is being done by AURA.
 */
function isFullPaymentDoneByAura(cart) {
  if (cart.cart.totals !== undefined
    && Object.keys(cart.cart.totals).length !== 0
    && cart.cart.totals.balancePayable <= 0) {
    return true;
  }

  return false;
}

/**
 * Utility function to check if given payment method is unsupported with Aura.
 */
function isUnsupportedPaymentMethod(paymentMethod) {
  const { auraUnsupportedPaymentMethods } = getAuraConfig();

  return auraUnsupportedPaymentMethods.includes(paymentMethod);
}

export {
  getUserInput,
  processCheckoutCart,
  getMembersToEarnMessage,
  redeemAuraPoints,
  isPaymentMethodSetAsAura,
  isFullPaymentDoneByAura,
  isUnsupportedPaymentMethod,
};
