import { hasValue } from '../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../js/utilities/error';
import logger from '../../../js/utilities/logger';
import { callDrupalApi, callMagentoApi } from '../../../js/utilities/requestHelper';

/**
 * Utility function to call api to get user info.
 */
const getCustomerDetails = async () => {
  // Extract the user details from drupal settings.
  const { userDetails } = drupalSettings;
  if (!(hasValue(userDetails)
    && hasValue(userDetails.userEmailID))) {
    logger.error('Error while trying to prepare data for get customer detail request. Data: @request_data', {
      '@request_data': JSON.stringify(userDetails),
    });
    return getErrorResponse('Request data is missing.', 404);
  }

  // Call the customer API to get the user details.
  return callMagentoApi('/V1/customers/me', 'GET').then((response) => {
    if (hasValue(response.data.error)) {
      logger.notice('Error while trying to get customer details. Request Data: @data. Message: @message', {
        '@data': JSON.stringify(response.data),
        '@message': response.data.error_message,
      });
      return response.data;
    }

    return response;
  });
};

/**
 * Utility function to update the user info.
 *
 * @param {object} updatedUserDetails
 *
 * @returns {object}
 *   The customer info object.
 */
const updateCustomerDetails = async (updatedUserDetails) => callMagentoApi('/V1/customers/me', 'PUT',
  { customer: updatedUserDetails }).then((response) => {
  if (hasValue(response.data.error)) {
    logger.warning('Error while trying to update customer details. Request Data: @data. Message: @message', {
      '@data': JSON.stringify(response.data),
      '@message': response.data.error_message,
    });
    return response.data;
  }

  return response;
});

/**
 * Utility function to get the address book info.
 *
 * @returns {object|boolean}
 *   The address book info.
 */
const getAddressbookInfo = async () => {
  const response = await callDrupalApi('/rest/v1/get-addressbook-info', 'GET');
  if (hasValue(response)
    && hasValue(response.data)) {
    return response.data;
  }
  return false;
};

export {
  getCustomerDetails,
  updateCustomerDetails,
  getAddressbookInfo,
};
