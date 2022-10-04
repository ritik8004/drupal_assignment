import React from 'react';
import {
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
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

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
              ...getAuraDetailsDefaultState(),
            };

            dispatchCustomEvent('loyaltyCardRemovedFromCart', { stateValues });
            removeFullScreenLoader();
            return;
          }

          // For add action.
          stateValues = {
            loyaltyStatus: result.data.data.apc_link || 0,
            points: result.data.data.apc_points || 0,
            cardNumber: result.data.data.apc_identifier_number || '',
            tier: result.data.data.tier_code || '',
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
 * Utility function points to earn message.
 */
function getMembersToEarnMessage(pointsToEarn) {
  const toEarnMessageP1 = `${getStringMessage('checkout_members_will_earn')} `;
  const toEarnMessageP2 = ` ${getStringMessage('checkout_with_this_purchase')}`;

  return (
    <span className="spc-checkout-aura-points-to-earn">
      { toEarnMessageP1 }
      <PointsString points={pointsToEarn} />
      { toEarnMessageP2 }
    </span>
  );
}

/**
 * Helper function to redeem points.
 */
function redeemAuraPoints(data, context = 'aura') {
  let stateValues = {};

  const apiData = window.auraBackend.processRedemption(data, context);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      removeFullScreenLoader();
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            balancePayable: result.data.data.balancePayable,
            paidWithAura: result.data.data.paidWithAura,
            balancePoints: result.data.data.balancePoints,
            // Adding an extra total balance payable attribute, so that we can use this
            // in egift.
            // Doing this because while removing AURA points, we remove the Balance
            // Payable attribute from cart total.
            totalBalancePayable: result.data.data.totalBalancePayable,
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
    && cart.cart.totals.balancePayable <= 0
    && (cart.cart.totals.paidWithAura === cart.cart.totals.base_grand_total)) {
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

/**
 * Helper function to get aura points to earn from sales api.
 */
function getAuraPointsToEarn(items, cardNumber) {
  let stateValues = {};
  const apiData = window.auraBackend.getAuraPointsToEarn(items, cardNumber);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      removeFullScreenLoader();
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          stateValues = {
            auraPointsToEarn: result.data.data.apc_points,
          };
        }
      } else {
        stateValues = result.data || { error: true };
      }
      dispatchCustomEvent('auraPointsToEarnApiInvoked', { stateValues });
    });
  }
}

export {
  getUserInput,
  processCheckoutCart,
  getMembersToEarnMessage,
  redeemAuraPoints,
  isPaymentMethodSetAsAura,
  isFullPaymentDoneByAura,
  isUnsupportedPaymentMethod,
  getAuraPointsToEarn,
};
