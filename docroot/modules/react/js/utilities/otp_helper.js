import { callMagentoApi } from './requestHelper';
import logger from './logger';
import { hasValue } from './conditionsUtility';
import { getErrorResponse } from './error';

/**
 * Calls the API to send OTP.
 *
 * @param {string} mobile
 *   Mobile number.
 * @param {string} type
 *   Type of send otp request - registration or link card.
 *
 * @returns {Object}
 *   Return API response/error.
 */
const sendOtp = (mobile, type) => callMagentoApi(`/V1/sendotp/phonenumber/${mobile.replace('+', '')}/type/${type}`, 'GET')
  .then((response) => {
    const responseData = { status: response.data };

    if (hasValue(response.data.error)) {
      logger.notice('Error while trying to send otp on mobile number @mobile. Message: @message', {
        '@mobile': mobile,
        '@message': response.data.error_message,
      });

      return response.data;
    }

    return responseData;
  });

/**
 * Verify OTP.
 *
 * @param {string} mobile
 *   Mobile number.
 * @param {string} chosenCountryCode
 *   Country code.
 * @param {string} otp
 *   Otp value.
 * @param {string} type
 *   Type of otp request.
 *
 * @returns {Promise}
 *   Returns an object with status value or the error object in case of failure.
 */
const verifyOtp = (mobile, otp, type, chosenCountryCode) => {
  if (!hasValue(mobile) || !hasValue(otp) || !hasValue(type)) {
    logger.error('Error while trying to verify otp. Mobile number, OTP and type is required.');
    return { data: getErrorResponse('Mobile number, OTP and type is required.', 404) };
  }

  return callMagentoApi(`/V1/verifyotp/phonenumber/${chosenCountryCode}${mobile}/otp/${otp}/type/${type}`, 'GET')
    .then((response) => {
      const responseData = {
        status: response.data,
      };

      if (response.data.error) {
        logger.notice('Error while trying to verify otp for mobile number @mobile. OTP: @otp. Type: @type. Message: @message', {
          '@mobile': mobile,
          '@otp': otp,
          '@type': type,
          '@message': response.data.error_message,
        });

        return { data: response.data };
      }

      return { data: responseData };
    });
};

export {
  sendOtp,
  verifyOtp,
};
