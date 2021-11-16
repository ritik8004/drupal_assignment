import { callDrupalApi } from '../../../../alshaya_spc/js/backend/v2/common';
import logger from '../../../../alshaya_spc/js/utilities/logger';

/**
 * Update user's aura info.
 *
 * @param {Object} data
 *   User's aura info.
 *
 * @return {Promise}
 *   true if update is successful else false.
 */
const updateUserAuraInfo = async (data) => callDrupalApi('/update/user-aura-info', 'POST', { form_params: data })
  .then((response) => response.data)
  .catch((e) => {
    logger.error('Error occurred while updating user aura info. Data: @data. Message: @message', {
      '@data': JSON.stringify(data),
      '@message': e.message,
    });
  });

export default updateUserAuraInfo;
