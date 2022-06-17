import { hasValue } from '../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../js/utilities/error';
import { callHelloMemberApi } from '../../../js/utilities/helloMemberHelper';
import logger from '../../../js/utilities/logger';

/**
 * Get Apc customer hello member data.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
 */
const getApcCustomerData = async () => {
  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to get customer info. No user available in session. User id: @user_id. Customer id: @customer_id.', {
      '@user_id': uid,
      '@customer_id': customerId,
    });
    return getErrorResponse('No user available in session', 403);
  }

  const params = {
    customerId,
    programCode: 'hello_member',
  };

  return callHelloMemberApi('helloMemberGetCustomerData', 'GET', params)
    .then((response) => {
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch customer apc information for user with customer id @customerId. Message: @message', {
          '@customerId': customerId,
          '@message': message,
        });
        return getErrorResponse(message, 500);
      }
      return response;
    });
};

/**
 * Get Apc tier tracking data for hello member customer.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing tier tracking data in case of
 *   success or an error object in case of failure.
 */
const getApcTierProgressData = async () => {
  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to get customer info. No user available in session. User id: @user_id. Customer id: @customer_id.', {
      '@user_id': uid,
      '@customer_id': customerId,
    });
    return getErrorResponse('No user available in session', 403);
  }

  const params = {
    customerId,
    programCode: 'hello_member',
  };

  return callHelloMemberApi('helloMemberGetApcTierProgressData', 'GET', params)
    .then((response) => {
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch tracking tier information for user with customer id @customerId. Message: @message', {
          '@customerId': customerId,
          '@message': message,
        });
        return getErrorResponse(message, 500);
      }
      return response;
    });
};

export {
  getApcCustomerData,
  getApcTierProgressData,
};
