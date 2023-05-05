import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import logger from '../../../../js/utilities/logger';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import auraErrorCodes from '../utility/error';
import { sendOtp, verifyOtp } from '../../../../js/utilities/otp_helper';
import { search, searchUserDetails } from './search_helper';
import validateInput from './validation_helper';
import { getErrorResponse } from '../../../../js/utilities/error';
import {
  getCustomerInfo,
  getCustomerPoints,
  getCustomerTier,
  getCustomerProgressTracker,
  setLoyaltyCard,
  prepareAuraUserStatusUpdateData,
  getCustomerRewardActivity,
} from './customer_helper';
import { formatDate } from '../../utilities/reward_activity_helper';
import { getPaymentMethodSetOnCart } from '../../../../alshaya_spc/js/backend/v2/checkout.payment';
import { prepareRedeemPointsData, redeemPoints } from './redemption_helper';
import { isUnsupportedPaymentMethod } from '../../../../alshaya_spc/js/aura-loyalty/components/utilities/checkout_helper';
import { isUserAuthenticated } from '../../../../js/utilities/helper';

/**
 * Global object to help perform Aura activities for V2.
 */
window.auraBackend = window.auraBackend || {};

/**
 * Performs the final step of the signup process for the user.
 *
 * @param {object} data
 *   Object containing items like firstname, lastname, email and phone/mobile.
 *
 * @returns {Promise}
 *   The promise object which resolves to the response data and status in case
 * of success and the error object in case of error.
 */
window.auraBackend.loyaltyClubSignUp = async (data) => {
  if (!hasValue(data.firstname) || !hasValue(data.lastname)) {
    logger.warning('Error while trying to do loyalty club sign up. First name and last name is required. Data: @data', {
      '@data': JSON.stringify(data),
    });

    return { data: getErrorResponse('INVALID_NAME_ERROR', 500) };
  }

  let validationResult = validateInput('email', data.email);
  if (hasValue(validationResult.error)) {
    return { data: validationResult };
  }

  validationResult = validateInput('mobile', data.mobile);
  if (hasValue(validationResult.error)) {
    return { data: validationResult };
  }

  // Call search API to check if given mobile number is already registered or
  // not.
  let searchResponse = await search('phone', data.mobile);
  // Check for backend error.
  if (hasValue(searchResponse.error)) {
    return searchResponse;
  }

  // Check if the mobile number is already registered.
  if (hasValue(searchResponse.data.apc_identifier_number)) {
    logger.error('Error while trying to do loyalty club sign up. Mobile number @mobile is already registered.', {
      '@mobile': data.mobile,
    });

    return {
      data: getErrorResponse(
        auraErrorCodes.MOBILE_ALREADY_REGISTERED_MSG,
        auraErrorCodes.MOBILE_ALREADY_REGISTERED_CODE,
      ),
    };
  }

  // Call search API to check if given email is already registered or not.
  searchResponse = await search('email', data.email);
  // Check for backend error.
  if (hasValue(searchResponse.error)) {
    return searchResponse;
  }

  // Check if email address is already register.
  if (hasValue(searchResponse.data.apc_identifier_number)) {
    logger.error('Error while trying to do loyalty club sign up. Email address @email is already registered.', {
      '@email': data.email,
    });

    return {
      data: getErrorResponse(
        auraErrorCodes.EMAIL_ALREADY_REGISTERED_MSG,
        auraErrorCodes.EMAIL_ALREADY_REGISTERED_CODE,
      ),
    };
  }

  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const requestData = {};
  requestData.customer = Object.assign(data, { isVerified: 'Y' });

  if (hasValue(customerId)) {
    requestData.customer.customerId = customerId;
  }

  const response = await callMagentoApi('/V1/customers/quick-enrollment', 'POST', requestData);
  if (hasValue(response.data.error)) {
    // Check if API returns error.
    if (response.status === 200) {
      logger.notice('Error while trying to do loyalty club sign up. Request Data: @data, Message: @message', {
        '@data': JSON.stringify(data),
        '@message': response.data.message,
      });
      return { data: getErrorResponse(response.data.message) };
    }

    // This means backend error has occured.
    logger.notice('Error while trying to do loyalty club sign up. Backend Error. Request Data: @data, Message: @message', {
      '@data': JSON.stringify(data),
      '@message': response.data.error_message,
    });
    return response.data;
  }

  const responseData = {
    status: true,
    data: response,
  };

  return responseData;
};

/**
 * Sends OTP.
 *
 * @param {object} data
 *   The data to send to the API.
 *
 * @returns {Object}
 *   Return API response status.
 */
window.auraBackend.sendSignUpOtp = async (mobile, chosenCountryCode) => {
  // Call search API to check if given mobile number is already registered or
  // not.
  const searchResponse = await search('phone', `${chosenCountryCode}${mobile}`);
  // Check for backend error.
  if (hasValue(searchResponse.error)) {
    return searchResponse;
  }
  // Check if mobile number is already registered.
  if (hasValue(searchResponse.data.apc_identifier_number)) {
    logger.error('Error while trying to send otp. Mobile number @mobile is already registered.', {
      '@mobile': mobile,
    });
    return {
      data: getErrorResponse(
        auraErrorCodes.MOBILE_ALREADY_REGISTERED_MSG,
        auraErrorCodes.MOBILE_ALREADY_REGISTERED_CODE,
      ),
    };
  }

  // Send otp for the given mobile number.
  const responseData = await sendOtp(`${chosenCountryCode}${mobile}`, 'reg');
  // Check for backend error.
  if (hasValue(responseData.error)) {
    return responseData;
  }

  return { data: responseData };
};

/**
 * Verifies the OTP entered by the user.
 *
 * @param {string} mobile
 *   Mobile number.
 * @param {string} otp
 *   Otp value.
 * @param {string} type
 *   Type of action for which otp is generated, eg. registration.
 * @param {string} chosenCountryCode
 *   The country code value.
 *
 * @returns {Promise}
 *   Returns an object with status value or the error object in case of failure.
 */
window.auraBackend.verifyOtp = (mobile, otp, type, chosenCountryCode) => verifyOtp(
  mobile,
  otp,
  type,
  chosenCountryCode,
);

/**
 * Send Link card OTP.
 *
 * @param {string} type
 *   The field for searching.
 * @param {string} value
 *   The field value.
 *
 * @returns {Promise}
 *   Returns a promise which resolves to an object.
 * On error, the error object is returned.
 * On success, the success object is returned containing specific data.
 */
window.auraBackend.sendLinkCardOtp = async (type, value) => {
  let responseData = {};

  const searchResponse = await searchUserDetails(type, value);

  if (hasValue(searchResponse.error)) {
    logger.error('Error while trying to search mobile number to send link card OTP. Request Data: @data', {
      '@data': JSON.stringify({ type, value }),
    });
    return searchResponse.custom ? { data: searchResponse } : searchResponse;
  }

  if (!hasValue(searchResponse.data.mobile)) {
    logger.error('Error while trying to send link card OTP. Mobile number not found. Request Data: @data', {
      '@data': JSON.stringify({ type, value }),
    });
    return { data: getErrorResponse(auraErrorCodes.NO_MOBILE_FOUND_MSG, 404) };
  }

  responseData = await sendOtp(searchResponse.data.mobile, 'link');

  if (hasValue(responseData.error)) {
    logger.error('Error while trying to send link card OTP. Backend error. Request Data: @data.', {
      '@data': JSON.stringify({ type, value }),
    });
    return responseData;
  }

  if (hasValue(responseData.status)) {
    responseData.mobile = searchResponse.data.mobile;
    responseData.cardNumber = searchResponse.data.apc_identifier_number;
  }

  return { data: responseData };
};

/**
 * Get the loyalty customer details.
 *
 * @returns {Object}
 *   Return customer data from API response.
 */
window.auraBackend.getCustomerDetails = async (data = {}) => {
  // Get user details from drupalSettings.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;
  const fetchStatus = data.fetchStatus !== undefined ? data.fetchStatus : true;
  const fetchPoints = data.fetchPoints !== undefined ? data.fetchPoints : true;
  const fetchTier = data.fetchTier !== undefined ? data.fetchTier : true;
  let responseData = {};

  if (!hasValue(customerId) || !hasValue(uid)) {
    logger.warning('Error while trying to fetch loyalty points for customer. No customer available in session. Customer Id: @customerId, User Id: @uid', {
      '@customerId': customerId,
      '@uid': uid,
    });

    return getErrorResponse('No user in session', 404);
  }

  // Call helper to get customer information only if fetch status
  // is not false.
  if (fetchStatus) {
    const customerInfo = await getCustomerInfo(customerId);

    if (hasValue(customerInfo.error)) {
      logger.error('Error while trying to fetch customer information for user with customer id @customerId. Message: @message', {
        '@customerId': customerId,
        '@message': customerInfo.error_message || '',
      });
      return getErrorResponse(customerInfo.error_message, customerInfo.error_code);
    }

    // If aura status is 0 i.e user is not signed up in Aura then no need
    // to fetch other details, return from here.
    if (customerInfo.auraStatus === 0) {
      return customerInfo;
    }

    responseData = { ...responseData, ...customerInfo };
  }

  // Call helper to get customer point details only if fetch points
  // is not false.
  if (fetchPoints) {
    const customerPoints = await getCustomerPoints(customerId);

    if (hasValue(customerPoints.error)) {
      logger.error('Error while trying to fetch customer information for user with customer id @customerId. Message: @message', {
        '@customerId': customerId,
        '@message': customerPoints.error_message || '',
      });
      return getErrorResponse(customerPoints.error_message, customerPoints.error_code);
    }

    responseData = { ...responseData, ...customerPoints };
  }

  // Call helper to get customer tier details only if fetch tier
  // is not false.
  if (fetchTier) {
    const customerTier = await getCustomerTier(customerId);

    if (hasValue(customerTier.error)) {
      logger.error('Error while trying to fetch customer information for user with customer id @customerId. Message: @message', {
        '@customerId': customerId,
        '@message': customerTier.error_message || '',
      });
      return getErrorResponse(customerTier.error_message, customerTier.error_code);
    }

    responseData = { ...responseData, ...customerTier };
  }

  return { data: responseData };
};

/**
 * Fetches progress tracker for the current user.
 *
 * @returns {Object}
 *   Return progress tracker data from API response.
 */
window.auraBackend.getProgressTracker = async () => {
  // Get user details from drupalSettings.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  if (!hasValue(customerId) || !hasValue(uid)) {
    logger.warning('Error while trying to get progress tracker of the user. No customer available in session. Customer Id: @customerId, User Id: @uid', {
      '@customerId': customerId,
      '@uid': uid,
    });

    return getErrorResponse('No user in session', 404);
  }

  const progressTracker = await getCustomerProgressTracker(customerId);

  if (hasValue(progressTracker.error)) {
    logger.error('Error while trying to get progress tracker of the user with customer id @customerId. Message: @message', {
      '@customerId': customerId,
      '@message': progressTracker.error_message || '',
    });
    return getErrorResponse(progressTracker.error_message, progressTracker.error_code);
  }

  const responseData = {
    status: true,
    data: progressTracker,
  };

  return { data: responseData };
};

/**
 * Set/Unset loyalty card in cart.
 *
 * @param {string} action
 *   The action, eg. 'add'.
 * @param {string} type
 *   The input value type.
 * @param {string} value
 *   The input value.
 * @param {string} context
 *   The context eg. aura or hello_member.
 *
 * @returns {Promise}
 *   A promise that contains the data and status in case of success and error
 * object in case of failure.
 */
window.auraBackend.updateLoyaltyCard = async (action, type, value, context) => {
  let responseData = {};
  const inputData = { action, type, value };

  // Check if action is not empty.
  if (!hasValue(action)) {
    logger.error('Error while trying to set loyalty card in cart. Action key `add/remove` is missing. Request Data: @data', {
      '@data': JSON.stringify(inputData),
    });
    return { data: getErrorResponse('Action key `add/remove` is missing.', 404) };
  }

  // Check if required data is present in request for `add` action.
  if (action === 'add' && (!hasValue(type) || !hasValue(value))) {
    logger.error('Error while trying to set loyalty card in cart. Required parameters missing. Request Data: @data', {
      '@data': JSON.stringify(inputData),
    });

    let error = '';
    if (type === 'email') {
      error = auraErrorCodes.EMPTY_EMAIL;
    } else if (type === 'apcNumber') {
      error = auraErrorCodes.EMPTY_CARD;
    } else if (type === 'phone') {
      error = auraErrorCodes.EMPTY_MOBILE;
    }

    return { data: getErrorResponse(error, 'MISSING_DATA') };
  }

  let identifierNo = '';
  let searchResponse = {};
  if (action === 'add') {
    searchResponse = await searchUserDetails(inputData.type, inputData.value, context);

    if (hasValue(searchResponse.error)) {
      logger.warning('Error while trying to set loyalty card in cart. No card found. Request Data: @data.', {
        '@data': JSON.stringify(inputData),
      });
      return { data: searchResponse };
    }
    identifierNo = searchResponse.data.apc_identifier_number;
  }

  responseData = {
    data: hasValue(searchResponse.data) ? searchResponse.data : {},
  };
  // Get cart id from session.
  const cartId = window.commerceBackend.getCartId();

  if (hasValue(cartId)) {
    const response = await setLoyaltyCard(identifierNo, cartId);
    if (hasValue(response.error)) {
      return { data: response };
    }
    responseData = {
      status: response,
      data: hasValue(searchResponse.data) ? searchResponse.data : {},
    };
  }

  return { data: responseData };
};

/**
 * Update User's AURA Status.
 *
 * @param {Object} inputData
 *   Input data object.
 *
 * @returns {Promise}
 *   Return success/failure response.
 */
window.auraBackend.updateUserAuraStatus = async (inputData) => {
  const data = prepareAuraUserStatusUpdateData(inputData);

  if (hasValue(data.error)) {
    logger.error('Error while trying to update user AURA Status. Data: @data.', {
      '@data': JSON.stringify(data),
    });
    return { data };
  }

  const { customerId } = drupalSettings.userDetails;
  // Get user details from session.
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to update user AURA Status. No user available in session. User id from request: @uid.', {
      '@uid': inputData.uid,
    });
    return getErrorResponse('No user available in session', 404);
  }

  // Check if uid in the request matches the one in session.
  if (uid !== inputData.uid) {
    logger.error('Error while trying to update user AURA Status. User id in request doesn\'t match the one in session. User id from request: @req_uid. User id in session: @session_uid.', {
      '@req_uid': inputData.uid,
      '@session_uid': uid,
    });
    return getErrorResponse("User id in request doesn't match the one in session.", 404);
  }

  data.statusUpdate.customerId = customerId;

  const response = await callMagentoApi('/V1/customers/apc-status-update', 'POST', data);
  if (hasValue(response.data.error)) {
    return { data: response.data };
  }

  const responseData = {
    status: response.data,
  };
  // Return, if status failed to update.
  if (!response.data) {
    return { data: responseData };
  }

  // Do not fetch custom data from apcNumber if,
  // it is an update is for not you(unlinking aura).
  if (data.statusUpdate.link !== 'N') {
    let customerData = {};
    const searchResponse = await search('apcNumber', data.statusUpdate.apcIdentifierId);
    if (hasValue(searchResponse.error)) {
      return { data: searchResponse };
    }

    if (hasValue(searchResponse.data.is_fully_enrolled)) {
      const customerInfo = getCustomerInfo(customerId);
      const customerPoints = getCustomerPoints(customerId);
      const customerTier = getCustomerTier(customerId);

      const values = await Promise.all([customerInfo, customerPoints, customerTier]);
      values.forEach((value) => {
        // If an API call throws error, ignore it.
        if (!hasValue(value.error)) {
          customerData = Object.assign(customerData, value);
        }
      });
      customerData.isFullyEnrolled = searchResponse.data.is_fully_enrolled;
    }

    responseData.data = hasValue(customerData)
      ? customerData
      : { auraStatus: searchResponse.data.apc_link };
  }
  return { data: responseData };
};

/**
 * Redeem loyalty points.
 *
 * @param {Object} data
 *   Data for the API call.
 *
 * @param {Object} context
 *   If context is aura or hello member.
 *
 * @returns {Object}
 *   Points and other data in case of success or error in case of failure.
 */
window.auraBackend.processRedemption = async (data, context = 'aura') => {
  let message = '';

  const cartId = window.commerceBackend.getCartId();
  if (!hasValue(cartId)) {
    message = 'Error while trying to redeem aura points. Cart is not available for the user.';
    logger.error(message);
    return { data: getErrorResponse(message, 404) };
  }

  const paymentMethodSetOnCart = await getPaymentMethodSetOnCart();
  if (hasValue(paymentMethodSetOnCart)
    && isUnsupportedPaymentMethod(paymentMethodSetOnCart)
  ) {
    logger.error(`Error while trying to redeem aura points. Selected payment method ${paymentMethodSetOnCart} is unsupported with Aura.`);
    return { data: getErrorResponse(message, 404) };
  }

  if (context !== 'hello_member') {
    // Check if required data is present in request.
    if (!hasValue(data.userId)) {
      message = 'Error while trying to redeem aura points. User Id is required for the feature @context.';
      logger.error(`${message} Data: @requestData`, {
        '@requestData': JSON.stringify(data),
        '@context': context,
      });
      return { data: getErrorResponse(message, 404) };
    }

    // Get user details from session.
    const { uid } = drupalSettings.user;

    // Check if uid in the request matches the one in session.
    if (parseInt(uid, 10) !== parseInt(data.userId, 10)) {
      logger.error("Error while trying to redeem aura points for feature @context. User id in request doesn't match the one in session. User id from request: @reqUid. User id in session: @sessionUid.", {
        '@reqUid': data.userId,
        '@sessionUid': uid,
        '@context': context,
      });
      return { data: getErrorResponse("User id in request doesn't match the one in session.", 404) };
    }
  }

  // Check if required data is present in request.
  if (!hasValue(data.cardNumber)) {
    message = 'Error while trying to redeem aura points. Card Number is required for feature @context.';
    logger.error(`${message} Data: @requestData`, {
      '@requestData': JSON.stringify(data),
      '@context': context,
    });
    return { data: getErrorResponse(message, 404) };
  }

  const redeemPointsRequestData = prepareRedeemPointsData(data, cartId);

  if (hasValue(redeemPointsRequestData.error)) {
    logger.error('Error while trying to create redeem points request data. Request data: @requestData. Message: @message', {
      '@requestData': JSON.stringify(data),
      '@message': redeemPointsRequestData.error_message,
    });
    return { data: redeemPointsRequestData };
  }

  // API call to redeem points.
  const responseData = await redeemPoints(data.cardNumber, redeemPointsRequestData);
  if (hasValue(responseData.error)) {
    logger.notice('Error while trying to redeem aura points. Request Data: @request_data. Message: @message', {
      '@request_data': JSON.stringify(data),
      '@message': responseData.message,
    });
    return { data: responseData };
  }

  return { data: responseData };
};

/** Fetches reward activity for the current user.
 *
 * @param {string} fromDate
 *   From date.
 * @param {string} toDate
 *   To date.
 * @param {string} maxResults
 *   Max results to fetch.
 * @param {string} channel
 *   Online(K)/ InStore(V).
 * @param {string} partnerCode
 *   The brand code.
 * @param {string} duration
 *   The duration of transactions.
 *
 * @returns {Object}
 *   Return reward activity data from API response.
 */
window.auraBackend.getRewardActivity = async (fromDate = '', toDate = '', maxResults = 0, channel = '', partnerCode = '', duration = '') => {
  // Get user details from drupalSettings.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  if (!hasValue(customerId) || !hasValue(uid)) {
    logger.warning('Error while trying to get reward activity of the user. No customer available in session. Customer Id: @customerId, User Id: @uid', {
      '@customerId': customerId,
      '@uid': uid,
    });

    return getErrorResponse('No user in session', 404);
  }

  // API call to get reward activity.
  let rewardActivity = await getCustomerRewardActivity(
    customerId,
    fromDate,
    toDate,
    maxResults,
    channel,
    partnerCode,
  );

  // Check if request is to get last transaction of the user and response is not empty.
  if (!hasValue(fromDate)
    && !hasValue(toDate)
    && parseInt(maxResults, 10) === 1
    && hasValue(rewardActivity)) {
    // If last transaction is before given duration, return empty.
    const lastTransactionData = rewardActivity.shift();
    const currentDate = new Date();
    const dateOfDuration = currentDate.setMonth(currentDate.getMonth() - duration);

    if (Date.parse(lastTransactionData.date) < dateOfDuration) {
      return {
        data: {
          status: true,
          data: [],
        },
      };
    }

    // API call to get reward activity data.
    const lastTransactionDate = new Date(lastTransactionData.date);
    rewardActivity = await getCustomerRewardActivity(
      customerId,
      formatDate(new Date(lastTransactionDate.getFullYear(), lastTransactionDate.getMonth()), 'YYYY-MM-DD'),
      formatDate(new Date(lastTransactionDate.getFullYear(), lastTransactionDate.getMonth(), lastTransactionDate.getDate()), 'YYYY-MM-DDT'),
      0,
      channel,
      partnerCode,
    );
  }

  if (hasValue(rewardActivity.error)) {
    logger.error('Error while trying to get reward activity of the user with customer id @customerId. Message: @message', {
      '@customerId': customerId,
      '@message': rewardActivity.error_message || '',
    });
    return getErrorResponse(rewardActivity.error_message, rewardActivity.error_code);
  }

  const responseData = {
    status: true,
    data: rewardActivity,
  };

  return { data: responseData };
};

/**
 * Fetches aura points to earn for the current user.
 *
 * @returns {Object}
 *   Return aura points to earn.
 */
window.auraBackend.getAuraPointsToEarn = async (cardNumber) => {
  const cartId = window.commerceBackend.getCartId();
  if (!hasValue(cartId)) {
    logger.error('Error while trying to set loyalty card in cart. Cart id not available.');
    return getErrorResponse('Cart id not available.', 404);
  }

  let endpoint = '/V1/apc/guest/simulate/sales';
  if (hasValue(cardNumber)) {
    endpoint = isUserAuthenticated()
      ? `/V1/apc/${cardNumber}/simulate/sales`
      : `/V1/apc/${cardNumber}/guest/simulate/sales`;
  }

  // Prepare request data.
  const requestData = {
    sales: {
      quote_id: cartId,
    },
  };

  const response = await callMagentoApi(endpoint, 'POST', requestData);

  // Check if API returns error.
  if (hasValue(response.data.error)) {
    logger.notice('Error while trying to get aura points to earn. Request Data: @data, Endpoint: @endpoint, Message: @message', {
      '@data': JSON.stringify(requestData),
      '@endpoint': endpoint,
      '@message': response.data.error_message,
    });
    return getErrorResponse(response.data.error_message, response.data.error_code);
  }

  return { data: response };
};
