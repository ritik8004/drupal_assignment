import { callMagentoApi } from '../../../../alshaya_spc/js/backend/v2/common';
import logger from '../../../../alshaya_spc/js/utilities/logger';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import getErrorResponse from '../../../../js/utilities/error';

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
 * @returns {Object}
 *   Return API response status.
 */
const verifyOtp = (mobile, chosenCountryCode, otp, type) => {
  if (!hasValue(mobile) || !hasValue(otp) || !hasValue(type)) {
    logger.error('Error while trying to verify otp. Mobile number, OTP and type is required.');
    return { data: getErrorResponse('Mobile number, OTP and type is required.', 404) };
  }

  return callMagentoApi(`/verifyotp/phonenumber/${chosenCountryCode}${mobile}/otp/${otp}/type/${type}`, 'GET')
    .then((response) => {
      const responseData = {
        status: response,
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

export default verifyOtp;
