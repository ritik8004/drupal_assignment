import React from 'react';
import {
  getElementValue,
  showError,
  getPriceToPoint,
} from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../../js/utilities/strings';
import { postAPIData } from '../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import {
  removeFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import PointsString from './points-string';

/**
 * Utility function to get user input value.
 */
function getUserInput(linkCardOption, chosenCountryCode) {
  let elementValue = {};

  if (linkCardOption === 'mobile') {
    const mobile = getElementValue('spc-aura-link-card-input-mobile-mobile-number');

    if (mobile.length === 0 || mobile.match(/^[0-9]+$/) === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_mobile_number'));
      return {};
    }

    elementValue = {
      key: 'mobile',
      type: 'phone',
      value: chosenCountryCode + mobile,
    };
  } else if (linkCardOption === 'cardNumber') {
    const card = getElementValue('spc-aura-link-card-input-card')
      ? getElementValue('spc-aura-link-card-input-card').replace(/\s/g, '')
      : '';

    if (card.length === 0 || card.match(/^[0-9]+$/) === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_card'));
      return {};
    }

    elementValue = {
      key: 'cardNumber',
      type: 'apcNumber',
      value: card,
    };
  } else if (linkCardOption === 'email') {
    const email = getElementValue('spc-aura-link-card-input-email');

    if (email.length === 0 || email.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i) === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_email'));
      return {};
    }

    elementValue = {
      key: 'email',
      type: 'email',
      value: email,
    };
  }

  return elementValue;
}

/**
 * Helper function to search loyalty details based on
 * user input and attach the card to cart.
 */
function processCheckoutCart(data) {
  let stateValues = {};

  const apiUrl = 'cart/loyalty/update';
  const apiData = postAPIData(apiUrl, data);

  if (apiData instanceof Promise) {
    apiData.then((result) => {
      if (result.data !== undefined && result.data.error === undefined) {
        if (result.data.status) {
          let mobile; let
            userCountryCode = '';

          if (result.data.data.mobile) {
            const mobileWithoutPrefixPlus = result.data.data.mobile.replace('+', '');
            mobile = mobileWithoutPrefixPlus.substring(3);
            userCountryCode = mobileWithoutPrefixPlus.substring(0, 3);
          }

          stateValues = {
            loyaltyStatus: result.data.data.apc_link || 0,
            points: result.data.data.apc_points || 0,
            cardNumber: result.data.data.apc_identifier_number || '',
            tier: result.data.data.tier_info || '',
            email: result.data.data.email || '',
            mobile,
            userCountryCode,
          };
        }
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
  const toEarnMessageP1 = `${Drupal.t('Members will earn')} `;
  const toEarnMessageP2 = ` ${Drupal.t('with this purchase')}`;
  const points = getPriceToPoint(price);

  return (
    <span className="spc-checkout-aura-points-to-earn">
      { toEarnMessageP1 }
      <PointsString points={points} />
      { toEarnMessageP2 }
    </span>
  );
}

export {
  getUserInput,
  processCheckoutCart,
  getMembersToEarnMessage,
};
