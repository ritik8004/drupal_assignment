import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import auraErrorCodes from '../utility/error';
import validateInput from './validation_helper';
import { getErrorResponse } from '../../../../js/utilities/error';

/**
 * Call the search API for the provided params.
 *
 * @param {string} type
 *   The field for searching.
 * @param {string} value
 *   The field value.
 *
 * @return {Object}
 *   Return API response/error.
 */
const search = async (type, value) => {
  const endpoint = `/V1/customers/apc-search/${type}/${value}`;

  return callMagentoApi(endpoint, 'GET')
    .then((response) => {
      let responseData = null;

      if (hasValue(response.data.error)) {
        // The user is not found. So we return error.
        if (response.status === 200) {
          responseData = {
            status: false,
            data: response.data,
          };
          return responseData;
        }

        // This means that there is an error in the response.
        return response.data;
      }

      // The user is found. So we return true.
      responseData = {
        status: true,
        data: response.data,
      };
      return responseData;
    });
};

/**
 * Search based on type of input to get user details.
 *
 * @param {string} type
 *   The field for searching.
 * @param {string} value
 *   The field value.
 *
 * @returns {Promise}
 *   Promise that resolves to error on failure or user data on success.
 */
const searchUserDetails = (type, value) => {
  const validation = validateInput(type, value);

  if (hasValue(validation.error)) {
    return new Promise((resolve) => resolve(validation));
  }

  if (type === 'email') {
    // Call search api to get mobile number to send otp.
    return search('email', value).then((searchResponse) => {
      if (typeof searchResponse.status !== 'undefined' && !searchResponse.status) {
        return getErrorResponse(
          auraErrorCodes.EMAIL_NOT_REGISTERED,
          auraErrorCodes.INVALID_EMAIL,
          true,
        );
      }

      return searchResponse;
    });
  }

  if (type === 'cardNumber' || type === 'apcNumber') {
    // Call search api to get mobile number to send otp.
    return search('apcNumber', value).then((searchResponse) => {
      if (typeof searchResponse.status !== 'undefined' && !searchResponse.status) {
        return getErrorResponse(
          auraErrorCodes.INCORRECT_CARDNUMBER,
          auraErrorCodes.INVALID_CARDNUMBER,
          true,
        );
      }

      return searchResponse;
    });
  }

  if (type === 'mobile' || type === 'phone') {
    // Call search api to verify mobile number to send otp.
    return search('phone', value).then((searchResponse) => {
      if (typeof searchResponse.status !== 'undefined' && !searchResponse.status) {
        return getErrorResponse(
          auraErrorCodes.MOBILE_NOT_REGISTERED,
          auraErrorCodes.INVALID_MOBILE,
          true,
        );
      }

      return searchResponse;
    });
  }

  return new Promise((resolve) => resolve({}));
};

export {
  search,
  searchUserDetails,
};
