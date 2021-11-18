import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import logger from '../../../../js/utilities/logger';
import { getErrorResponse } from '../../../../js/utilities/error';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

/**
 * Get Customer Information.
 *
 * @return array
 *   Return API response/error.
 */
const getCustomerInfo = async (customerId) => {
  const endpoint = `/V1/customers/apcCustomerData/${customerId}`;

  return callMagentoApi(endpoint, 'GET')
    .then((response) => {
      if (hasValue(response.data.error)) {
        logger.error('Error while trying to fetch customer information for user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': response.data.error_message || '',
        });
        return getErrorResponse(response.data.error_message, response.data.error_code);
      }

      const responseData = {
        cardNumber: response.data.apc_identifier_number || '',
        auraStatus: response.data.apc_link || '',
        auraPoints: response.data.apc_points || 0,
        phoneNumber: response.data.apc_phone_number || '',
        firstName: response.data.apc_first_name || '',
        lastName: response.data.apc_last_name || '',
      };
      return responseData;
    });
};

/**
 * Get Customer Points.
 *
 * @return array
 *   Return API response/error.
 */
const getCustomerPoints = async (customerId) => {
  const endpoint = `/V1/customers/apc-points-balance/${customerId}`;

  return callMagentoApi(endpoint, 'GET')
    .then((response) => {
      if (hasValue(response.data.error)) {
        logger.error('Error while trying to fetch loyalty points for user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': response.data.error_message,
        });
        return getErrorResponse(response.data.error_message, response.data.error_code);
      }

      const responseData = {
        customerId: response.data.customer_id || '',
        cardNumber: response.data.apc_identifier_number || '',
        auraPoints: response.data.apc_points || 0,
        auraPointsToExpire: response.data.apc_points_to_expire || 0,
        auraPointsExpiryDate: response.data.apc_points_expiry_date || '',
        auraOnHoldPoints: response.data.apc_on_hold_points || 0,
      };
      return responseData;
    });
};

/**
 * Get Customer Tier.
 *
 * @return array
 *   Return API response/error.
 */
const getCustomerTier = async (customerId) => {
  const endpoint = `/V1/customers/apc-tiers/${customerId}`;

  return callMagentoApi(endpoint, 'GET')
    .then((response) => {
      if (hasValue(response.data.error)) {
        logger.error('Error while trying to fetch tier information for user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': response.data.error_message,
        });
        return getErrorResponse(response.data.error_message, response.data.error_code);
      }

      const responseData = {
        tier: response.data.tier_code || '',
      };
      return responseData;
    });
};

export {
  getCustomerInfo,
  getCustomerPoints,
  getCustomerTier,
};
