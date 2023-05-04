import React from 'react';
import { hasValue } from './conditionsUtility';
import { getErrorResponse } from './error';
import { isUserAuthenticated } from './helper';
import logger from './logger';
import { callMagentoApi } from './requestHelper';

/**
 * Helper function to check if Hello Member is enabled.
 */
export default function isHelloMemberEnabled() {
  return hasValue(drupalSettings.helloMember) && hasValue(drupalSettings.helloMember.status);
}

/**
 * Helper function to check if aura integration with hello member is enabled.
 */
export const isAuraIntegrationEnabled = () => isHelloMemberEnabled()
  && hasValue(drupalSettings.helloMember.auraIntegrationStatus);

/**
 * Helper function to get aura related config for hello memeber.
 */
export const getAuraFormConfig = () => {
  if (hasValue(drupalSettings.helloMember.auraFormConfig)) {
    return drupalSettings.helloMember.auraFormConfig;
  }
  return null;
};

/**
 * Helper function to get the customer info from user session.
 */
export const getHelloMemberCustomerInfo = () => {
  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to get customer info. No user available in session. User id: @user_id. Customer id: @customer_id.', {
      '@user_id': uid,
      '@customer_id': customerId,
    });
    return getErrorResponse('No user available in session', 403);
  }

  const params = {
    customerId,
    programCode: 'hello_member',
  };

  return params;
};

/**
 * Gets magento api endpoint by user role.
 *
 * @param {string} action
 *   Callname for the API.
 *
 * @returns {string}
 *   The api endpoint.
 */
export const getApiEndpoint = (action, params = {}, postParams) => {
  let endpoint = '';
  const endPointParams = params;
  switch (action) {
    case 'helloMemberGetCustomerData':
      endpoint = `/V1/customers/apcCustomerData/${endPointParams.customerId}`; // endpoint to check hello member customer data.
      break;
    case 'helloMemberGetTierProgressData':
      endpoint = `/V1/customers/apcTierProgressData/customerId/${endPointParams.customerId}`; // endpoint to check hello member customer data.
      break;
    case 'helloMemberCouponsList':
      endpoint = '/V1/hello-member/customers/coupons'; // endpoint to get hello member coupons list.
      break;
    case 'helloMemberCouponPage':
      endpoint = `/V1/hello-member/customers/coupons/id/${endPointParams.code}`; // endpoint to get hello member coupon details.
      break;
    case 'helloMemberOffersList':
      endpoint = '/V1/hello-member/customers/offers'; // endpoint to get hello member offers list.
      break;
    case 'helloMemberOfferPage':
      endpoint = `/V1/hello-member/customers/offers/code/${endPointParams.code}`; // endpoint to get hello member offer details.
      break;
    case 'helloMemberGetPointsHistory':
      endpoint = '/V1/customers/apcTransactions'; // endpoint to get hello member points history.
      break;
    case 'helloMemberGetDictionaryData':
      endpoint = `/V1/customers/apcDicData/${endPointParams.type}`; // endpoint to get hello member dictonary data.
      break;
    case 'helloMemberGetPointsEarned':
      // endpoint to get hello member points earned data for guest without identifier no.
      endpoint = '/V1/apc/guest/simulate/sales';
      if (hasValue(postParams.identifierNo)) {
        endpoint = isUserAuthenticated()
          ? `/V1/apc/${postParams.identifierNo}/simulate/sales` // endpoint to get hello member points earned data with identifier no.
          : `/V1/apc/${postParams.identifierNo}/guest/simulate/sales`; // endpoint to get hello member points earned data for guest with identifier no.
      }
      break;
    case 'helloMemberSetLoyaltyCard':
      endpoint = '/V1/customers/mine/set-loyalty-card'; // endpoint to set hello member loyalty card details.
      break;
    case 'helloMemberCustomerPhoneSearch':
      endpoint = `/V1/customers/apc-search/phone/${endPointParams.phoneNumber}`; // endpoint to search hello member by phone number.
      break;
    case 'addBonusVouchersToCart':
      endpoint = '/V1/hello-member/carts/mine/bonusVouchers';
      break;
    case 'addMemberOffersToCart':
      endpoint = '/V1/hello-member/carts/mine/memberOffers';
      break;
    case 'getCartData':
      endpoint = '/V1/carts/mine/getCart';
      break;
    case 'unsetLoyaltyCard':
      endpoint = isUserAuthenticated()
        ? '/V1/customers/mine/unset-loyalty-card'
        : '/V1/apc/unset-loyalty-card';
      break;
    case 'getAuraCustomerPoints':
      endpoint = `/V1/guest/apc-points-balance/identifierNo/${endPointParams.identifierNo}`;
      break;
    case 'helloMemberRemoveOffers':
      endpoint = '/V1/hello-member/carts/mine/memberOffers';
      break;
    case 'helloMemberRemovebonusVouchers':
      endpoint = '/V1/hello-member/carts/mine/bonusVouchers';
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  return endpoint;
};

/**
 * Gets hello member data response from magento api endpoint.
 *
 * @param {string} action
 *   Callname for the API.
 * @param {string} method
 *   The request method.
 * @param {object} postData
 *   The object containing post data
 * @param {boolean} bearerToken
 *   The bearerToken flag.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const callHelloMemberApi = (action, method, postData, postParams, bearerToken = true) => {
  const endpoint = getApiEndpoint(action, postData, postParams);
  return callMagentoApi(endpoint, method, postData, bearerToken);
};

/**
 * Get hello member customer data.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
 */
export const getHelloMemberCustomerData = async () => {
  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to get customer info. No user available in session. User id: @user_id. Customer id: @customer_id.', {
      '@user_id': uid,
      '@customer_id': customerId,
    });
    return getErrorResponse('No user available in session', 403);
  }

  const params = {
    customerId,
    programCode: 'hello_member',
  };

  return callHelloMemberApi('helloMemberGetCustomerData', 'GET', params)
    .then((response) => {
      const errorCode = hasValue(response.data.error_code) ? response.data.error_code : 500;
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.error_message) ? response.data.error_message : '';
        logger.error('Error while trying to fetch hello member customer information for user with customer id @customerId. Message: @message', {
          '@customerId': customerId,
          '@message': message,
        });
        return getErrorResponse(message, errorCode);
      }
      return response;
    });
};

/**
 * Get hello member dictionary data.
 *
 * @returns {Promise}
 *   Promise that resolves to an object which contains the response or
 * the error object.
 */
export const getHelloMemberDictionaryData = async (requestData) => callHelloMemberApi('helloMemberGetDictionaryData', 'GET', requestData)
  .then((response) => {
    if (response.status !== 200) {
      const message = hasValue(response.data.error_message) ? response.data.error_message : '';
      logger.error('Error while trying to call hello member dictionary data Api @params, Message: @message', {
        '@message': message,
        '@params': requestData,
      });
    }
    return response;
  });

/**
 * Utility function to get hello member points for given price.
 */
export const getPriceToHelloMemberPoint = (price, dictionaryData) => {
  if (hasValue(dictionaryData) && hasValue(dictionaryData.items)) {
    const accrualRatio = dictionaryData.items[0];
    const points = accrualRatio.value ? (price * parseFloat(accrualRatio.value)) : 0;
    return Math.floor(points);
  }
  return null;
};

/**
 * Fetches hello member points to earn for the current user.
 *
 * @returns {Object}
 *   Return hello member points to earn.
 */
export const getHelloMemberPointsToEarn = async (identifierNo) => {
  // Get cart id from session.
  const cartId = window.commerceBackend.getCartId();

  if (!hasValue(cartId)) {
    logger.error('Error while trying to set loyalty card in cart. Cart id not available.');
    return { data: getErrorResponse('Cart id not available.', 404) };
  }

  const requestData = {
    sales: {
      quote_id: cartId,
    },
    programCode: 'hello_member',
  };

  return callHelloMemberApi('helloMemberGetPointsEarned', 'POST', requestData, { identifierNo })
    .then((response) => {
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to get hello member points to earn. Message: @message', {
          '@message': message,
        });
        return getErrorResponse(message, 500);
      }
      return response;
    });
};

/**
 * Sets hello member loyalty card option during checkout.
 *
 * @param {string} identifierNo
 *   Identifier number.
 * @param {string} quoteId
 *   Quote/Cart ID.
 *
 * @returns {Promise}
 *   Promise that resolves to an object which contains the status true/false or
 * the error object.
 */
export const setHelloMemberLoyaltyCard = async (identifierNo, quoteId) => {
  const requestData = {
    quoteId,
    identifierNo,
    programCode: 'hello_member',
  };

  return callHelloMemberApi('helloMemberSetLoyaltyCard', 'POST', requestData)
    .then((response) => {
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to set loyalty card data for hello member. Message: @message', {
          '@message': message,
        });
        return getErrorResponse(message, 500);
      }
      return {
        status: response.data,
      };
    });
};

/**
 * Helper function to display an error message to the customer during CLM downtime.
 */
export const displayErrorMessage = (message) => <div className="hello-member-points-wrapper"><div className="hello-member-downtime-message">{ message }</div></div>;

/**
 * Helper function to check benefits channel.
 */
export const getBenefitTag = (responseData) => {
  if (hasValue(responseData) && hasValue(responseData.tag)) {
    return responseData.tag;
  }
  return null;
};

/**
 * Helper function to return button name for external benefits.
 *
 *  @param {boolean} returnTranslatable
 *  returns benefitText Translatable
 */
export const getExternalBenefitText = (responseData, returnTranslatable) => {
  let benefitText = null;
  if (hasValue(responseData) && hasValue(responseData.tag)) {
    switch (responseData.tag) {
      case 'ER':
        benefitText = 'Redeem Now';
        if (returnTranslatable) {
          benefitText = Drupal.t('Redeem Now', {}, { context: 'hello_member' });
        }
        break;
      case 'ES':
        benefitText = 'Shop Now';
        if (returnTranslatable) {
          benefitText = Drupal.t('Shop Now', {}, { context: 'hello_member' });
        }
        break;
      case 'EB':
        benefitText = 'Book Now';
        if (returnTranslatable) {
          benefitText = Drupal.t('Book Now', {}, { context: 'hello_member' });
        }
        break;
      default:
        break;
    }
  }
  return benefitText;
};
