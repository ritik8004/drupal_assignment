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
      // @todo We need to check for backend error here. Currently it is not
      // possible because in case of backend error and also if user is not found
      // on calling the Magento Search API, we get the error object here in
      // both the cases.
      // We are not able to distinguish between the 2 cases because of that.
      // So we need to find a way of handling the errors.
      const responseData = {
        status: true,
        data: response.data,
      };
      return responseData;
    });
};

export default search;
