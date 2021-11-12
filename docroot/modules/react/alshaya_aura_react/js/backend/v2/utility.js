import { callDrupalApi } from '../../../../alshaya_spc/js/backend/v2/common';
import logger from '../../../../alshaya_spc/js/utilities/logger';

/**
 * Returns the error in a specific format.
 *
 * @param {string} message
 *   The processed error message.
 * @param {string} code
 *   The error code.
 *
 * @returns {object}
 *   The object containing the error data.
 */
const getErrorResponse = (message, code) => ({
  error: true,
  // @todo: Process error message like in getErrorResponse().
  error_message: message,
  error_code: code,
});

/**
 * Update user's aura info.
 *
 * @param {Array} data
 *   User's aura info.
 *
 * @return {Promise}
 *   true if update is successful else false.
 */
const updateUserAuraInfo = async (data) => callDrupalApi('/update/user-aura-info', 'POST', data)
  .then((response) => response.data)
  .catch((e) => {
    logger.error('Error occurred while updating user aura info. Data: @data. Message: @message', {
      '@data': JSON.stringify(data),
      '@message': e.message,
    });
  });

export {
  getErrorResponse,
  updateUserAuraInfo,
};
