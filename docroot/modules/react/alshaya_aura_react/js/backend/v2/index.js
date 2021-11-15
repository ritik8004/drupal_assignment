import { callMagentoApi } from '../../../../alshaya_spc/js/backend/v2/common';
import logger from '../../../../alshaya_spc/js/utilities/logger';
import { hasValue, isObject } from '../../../../js/utilities/conditionsUtility';
import auraErrorCodes from '../utility/error';
import sendOtp from './otp_helper';
import search from './search_helper';
import updateUserAuraInfo from './utility';
import validateInput from './validation_helper';
import getErrorResponse from '../../../../js/utilities/error';

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
  const { uid } = drupalSettings.user;
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

  // On API success, update user AURA Status in Drupal for logged in user.
  if (hasValue(uid) && isObject(response) && hasValue(response.data.apc_link)) {
    const auraData = {
      uid,
      apcLinkStatus: response.data.apc_link,
    };
    const isUserAuraInfoUpdated = await updateUserAuraInfo(auraData);

    // Check if user aura status was updated successfully in Drupal.
    if (!isUserAuraInfoUpdated) {
      const message = 'Error while trying to update user AURA Status field in Drupal after loyalty club sign up.';
      logger.error(`${message}. User Id: @uid, Customer Id: @customer_id, Aura Status: @aura_status.`, {
        '@uid': uid,
        '@customer_id': customerId,
        '@aura_status': response.data.apc_link,
      });
      return { data: getErrorResponse(message, 500) };
    }
  }

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
