import { callMagentoApi } from '../../../../alshaya_spc/js/backend/v2/common';
import logger from '../../../../alshaya_spc/js/utilities/logger';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

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
    }

    return responseData;
  });

export default sendOtp;
