import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import logger from '../../../../js/utilities/logger';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import auraErrorCodes from '../utility/error';
import { sendOtp, verifyOtp } from '../../../../js/utilities/otp_helper';
import { search, searchUserDetails } from './search_helper';
import validateInput from './validation_helper';
import { getErrorResponse } from '../../../../js/utilities/error';
import { getCustomerInfo, getCustomerPoints, getCustomerTier } from './customer_helper';

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
    logger.notice('Error while trying to do loyalty club sign up. Request Data: @data, Message: @message', {
      '@data': JSON.stringify(data),
      '@message': response.data.error_message,
    });
    return { data: getErrorResponse(response.data.error_message, response.data.error_code) };
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
    return searchResponse.custom === true ? { data: searchResponse } : searchResponse;
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
  const fetchStatus = data.fetchStatus || true;
  const fetchPoints = data.fetchPoints || true;
  const fetchTier = data.fetchTier || true;
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
  if (fetchStatus === true) {
    const customerInfo = await getCustomerInfo(customerId);

    // @todo Add a check here to handle scenarios where customer doesn't
    // exist in Aura. We will check this once MDC API is updated.
    if (!hasValue(customerInfo.error)) {
      responseData = { ...responseData, ...customerInfo };
    }
  }

  // Call helper to get customer point details only if fetch points
  // is not false.
  if (fetchPoints === true) {
    const customerPoints = await getCustomerPoints(customerId);

    if (!hasValue(customerPoints.error)) {
      responseData = { ...responseData, ...customerPoints };
    }
  }

  // Call helper to get customer tier details only if fetch tier
  // is not false.
  if (fetchTier === true) {
    const customerTier = await getCustomerTier(customerId);

    if (!hasValue(customerTier.error)) {
      responseData = { ...responseData, ...customerTier };
    }
  }

  return { data: responseData };
};
