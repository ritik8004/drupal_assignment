import { callMagentoApi } from '../../../../alshaya_spc/js/backend/v2/common';
import logger from '../../../../alshaya_spc/js/utilities/logger';
import { getErrorResponse } from './utility';

/**
 * Search.
 *
 * @return array
 *   Return API response/error.
 */
const search = async (type, value) => {
  const endpoint = `/V1/customers/apc-search/${type}/${value}`;

  return callMagentoApi(endpoint, 'GET')
    .then((response) => {
      const responseData = {
        status: true,
        data: response,
      };
      return responseData;
    })
  // @todo Check if this ever gets executed.
    .catch((e) => {
      logger.notice('Error while trying to search APC user. Endpoint: @endpoint. Message: @message', {
        '@endpoint': endpoint,
        '@message': e.message,
      });
      return getErrorResponse(e.message, e.code);
    });
};

export default search;
