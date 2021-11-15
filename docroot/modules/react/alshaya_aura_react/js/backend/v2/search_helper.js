import { callMagentoApi } from '../../../../alshaya_spc/js/backend/v2/common';

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
      const responseData = {
        status: true,
        data: response.data,
      };
      return responseData;
    });
};

export default search;
