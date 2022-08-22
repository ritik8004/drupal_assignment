import { hasValue } from '../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../js/utilities/error';
import { callHelloMemberApi } from '../../../js/utilities/helloMemberHelper';
import logger from '../../../js/utilities/logger';
import { getPriceToHelloMemberPoint } from './utilities';

/**
 * Get hello member customer data.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
 */
const getHelloMemberCustomerData = async () => {
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
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch hello member customer information for user with customer id @customerId. Message: @message', {
          '@customerId': customerId,
          '@message': message,
        });
        return getErrorResponse(message, 500);
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
const getHelloMemberDictionaryData = async (requestData) => callHelloMemberApi('helloMemberGetDictionaryData', 'GET', requestData)
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
 * Get tier tracking data for hello member customer.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing tier tracking data in case of
 *   success or an error object in case of failure.
 */
const getHelloMemberTierProgressData = async () => {
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

  return callHelloMemberApi('helloMemberGetTierProgressData', 'GET', params)
    .then((response) => {
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch tracking tier information for user with customer id @customerId. Message: @message', {
          '@customerId': customerId,
          '@message': message,
        });
        return getErrorResponse(message, 500);
      }
      return response;
    });
};

/**
 * Get points history transactions data for hello member customer.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing tier tracking data in case of
 *   success or an error object in case of failure.
 */
const getHelloMemberPointsHistory = async (firstResult, pageSize) => {
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
    firstResult,
    pageSize,
  };

  return callHelloMemberApi('helloMemberGetPointsHistory', 'GET', params)
    .then((response) => {
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch tracking tier information for user with customer id @customerId. Message: @message', {
          '@customerId': customerId,
          '@message': message,
        });
        return getErrorResponse(message, 500);
      }
      return response;
    });
};

/**
 * Fetches hello member points to earn for the current user.
 *
 * @returns {Object}
 *   Return hello member points to earn.
 */
const getHelloMemberPointsToEarn = async (items, identifierNo) => {
  const { currencyCode } = drupalSettings.helloMember;

  if (!hasValue(items)) {
    logger.warning('Error while trying to get hello member points to earn. Product details is required.');
    return getErrorResponse('Product details is required.', 404);
  }

  // For guest user, there is no identifier number
  // calculate points to earn using dictionary API ratio.
  if (!hasValue(identifierNo)) {
    let totalPrice = 0;
    Object.entries(items).forEach(([, item]) => {
      totalPrice += (item.qty * item.finalPrice);
    });

    // If dictionary data does not exists in storage, we do api call.
    const requestData = {
      type: 'HM_ACCRUAL_RATIO',
      programCode: 'hello_member',
    };
    const response = await getHelloMemberDictionaryData(requestData);
    if (hasValue(response.data.error)) {
      const message = hasValue(response.data.message) ? response.data.message : '';
      logger.error('Error while trying to get hello member dictionary data. Message: @message', {
        '@message': message,
      });
      return getErrorResponse(message, 500);
    }
    if (hasValue(response.data) && !hasValue(response.data.error)) {
      return {
        data: { hm_points: getPriceToHelloMemberPoint(totalPrice, response.data) },
      };
    }
  }

  // Prepare request data.
  const products = [];

  Object.entries(items).forEach(([, item]) => {
    const itemDetails = {
      code: item.sku,
      quantity: item.qty,
      amount: item.qty * item.finalPrice,
    };
    products.push(itemDetails);
  });

  const requestData = {
    sales: {
      currencyCode,
      products,
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
const setHelloMemberLoyaltyCard = async (identifierNo, quoteId) => {
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
 * Search hello member customer phone number.
 *
 * @param {string} phoneNumber
 *   Customer phone number.
 *
 * @returns {Promise}
 *   Promise that resolves to an object which contains the response or
 * the error object.
 */
const helloMemberCustomerPhoneSearch = async (phoneNumber) => {
  const requestData = {
    phoneNumber,
    programCode: 'hello_member',
  };

  return callHelloMemberApi('helloMemberCustomerPhoneSearch', 'GET', requestData)
    .then((response) => {
      if (response.status !== 200) {
        const message = hasValue(response.data.error_message) ? response.data.error_message : '';
        logger.error('Error while trying to call customer phonesearch Api for hello member @params, Message: @message', {
          '@message': message,
          '@params': requestData,
        });
      }
      return response;
    });
};

export {
  getHelloMemberCustomerData,
  getHelloMemberTierProgressData,
  getHelloMemberPointsHistory,
  getHelloMemberPointsToEarn,
  setHelloMemberLoyaltyCard,
  helloMemberCustomerPhoneSearch,
  getHelloMemberDictionaryData,
};
