import { callMagentoApi } from '../../../../alshaya_spc/js/backend/v2/common';
import logger from '../../../../alshaya_spc/js/utilities/logger';
import { hasValue, isObject } from '../../../../js/utilities/conditionsUtility';
import auraErrorCodes from './error';
import search from './search_helper';
import { getErrorResponse, updateUserAuraInfo } from './utility';
import validateInput from './validation_helper';

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

    return getErrorResponse('INVALID_NAME_ERROR', 500);
  }

  let validationResult = validateInput('email', data.email);
  if (hasValue(validationResult.error)) {
    return validationResult;
  }

  validationResult = validateInput('mobile', data.mobile);
  if (hasValue(validationResult.error)) {
    return validationResult;
  }

  // Call search API to check if given mobile number is already registered or
  // not.
  let searchResponse = await search('phone', data.mobile);
  if (hasValue(searchResponse.data.apc_identifier_number)) {
    logger.error('Error while trying to do loyalty club sign up. Mobile number @mobile is already registered.', {
      '@mobile': data.mobile,
    });

    return getErrorResponse(
      auraErrorCodes.MOBILE_ALREADY_REGISTERED_MSG,
      auraErrorCodes.MOBILE_ALREADY_REGISTERED_CODE,
    );
  }

  // Call search API to check if given email is already registered or not.
  searchResponse = await search('email', data.email);
  if (hasValue(searchResponse.data.apc_identifier_number)) {
    logger.error('Error while trying to do loyalty club sign up. Email address @email is already registered.', {
      '@email': data.email,
    });

    return getErrorResponse(
      auraErrorCodes.EMAIL_ALREADY_REGISTERED_MSG,
      auraErrorCodes.EMAIL_ALREADY_REGISTERED_CODE,
    );
  }

  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;
  const requestData = {};
  requestData.customer = Object.assign(data, { isVerified: 'Y' });

  if (hasValue(customerId)) {
    requestData.customer.customerId = customerId;
  }

  let response = null;
  try {
    response = await callMagentoApi('/V1/customers/quick-enrollment', 'POST', requestData);
  } catch (e) {
    logger.notice('Error while trying to do loyalty club sign up. Request Data: @data, Message: @message', {
      '@data': JSON.stringify(data),
      '@message': e.message,
    });
    return getErrorResponse(e.message, e.code);
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
      logger.error(`${message} .  User Id: @uid, Customer Id: @customer_id, Aura Status: @aura_status.`, {
        '@uid': uid,
        '@customer_id': customerId,
        '@aura_status': response.data.apc_link,
      });
      return getErrorResponse(message, 500);
    }
  }

  return responseData;
};
