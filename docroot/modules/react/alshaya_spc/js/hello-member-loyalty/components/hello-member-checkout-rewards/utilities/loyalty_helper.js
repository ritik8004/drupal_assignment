import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import { callHelloMemberApi, getHelloMemberCustomerData, setHelloMemberLoyaltyCard } from '../../../../../../js/utilities/helloMemberHelper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../../../../js/utilities/error';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import logger from '../../../../../../js/utilities/logger';
import { removeFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import AuraHeaderIcon from '../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';

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

  const apiData = window.auraBackend.updateLoyaltyCard(data.action, data.type, value, 'hello_member');

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
 * Get Customer Points.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
 */
function getAuraCustomerPoints(identifierNo) {
  return callHelloMemberApi('getAuraCustomerPoints', 'GET', { identifierNo }, {}, false)
    .then((response) => {
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch loyalty points for user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': identifierNo,
          '@message': message,
        });
        return getErrorResponse(message, 500);
      }

      const responseData = {
        cardNumber: hasValue(response.data.apc_identifier_number) ? response.data.apc_identifier_number : '',
        auraPoints: hasValue(response.data.apc_points) ? response.data.apc_points : '',
        auraPointsToExpire: hasValue(response.data.apc_points_to_expire) ? response.data.apc_points_to_expire : '',
        auraOnHoldPoints: hasValue(response.data.apc_on_hold_points) ? response.data.apc_on_hold_points : '',
        auraPointsExpiryDate: hasValue(response.data.apc_points_expiry_date) ? response.data.apc_points_expiry_date : '',
      };
      return responseData;
    });
}

/**
 * Apply hello member loyalty on cart Id for current customer.
 *
 * @param {String} cartId
 *   Cart Id.
 */
function applyHelloMemberLoyalty(cartId) {
  const hmCustomerData = getHelloMemberCustomerData();
  if (hmCustomerData instanceof Promise) {
    hmCustomerData.then((response) => {
      if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)
        && hasValue(response.data.apc_identifier_number)) {
        setHelloMemberLoyaltyCard(response.data.apc_identifier_number, cartId);
      } else if (hasValue(response.error)) {
        logger.error('Error while trying to set hello member loyalty card data: @data.', {
          '@data': JSON.stringify(response),
        });
      }
    });
  }
}

function getLoyaltySelectText(optionName, helloMemberPoints) {
  if (optionName === 'hello_member') {
    return parse(parse(Drupal.t('@hm_icon You’re earning @points points as an H&M member', {
      '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
      '@points': helloMemberPoints,
    }, { context: 'hello_member' })));
  }
  if (optionName === 'aura') {
    return parse(parse(Drupal.t('Earn/Redeem @aura_icon Points', {
      '@aura_icon': `<span class="hello-member-aura">${renderToString(<AuraHeaderIcon />)}</span>`,
    }, { context: 'hello_member' })));
  }
  return null;
}

export {
  processCheckoutCart,
  getAuraCustomerPoints,
  applyHelloMemberLoyalty,
  getLoyaltySelectText,
};
