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
const getCustomerInfo = (customerId) => {
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
        cardNumber: hasValue(response.data.apc_identifier_number) ? response.data.apc_identifier_number : '',
        auraStatus: hasValue(response.data.apc_link) ? response.data.apc_link : '',
        auraPoints: hasValue(response.data.apc_points) ? response.data.apc_points : 0,
        phoneNumber: hasValue(response.data.apc_phone_number) ? response.data.apc_phone_number : '',
        firstName: hasValue(response.data.apc_first_name) ? response.data.apc_first_name : '',
        lastName: hasValue(response.data.apc_last_name) ? response.data.apc_last_name : '',
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
const getCustomerPoints = (customerId) => {
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
        customerId: hasValue(response.data.customer_id) ? response.data.customer_id : '',
        cardNumber: hasValue(response.data.apc_identifier_number) ? response.data.apc_identifier_number : '',
        auraPoints: hasValue(response.data.apc_points) ? response.data.apc_points : '',
        auraPointsToExpire: hasValue(response.data.apc_points_to_expire) ? response.data.apc_points_to_expire : '',
        auraOnHoldPoints: hasValue(response.data.apc_on_hold_points) ? response.data.apc_on_hold_points : '',
        auraPointsExpiryDate: hasValue(response.data.apc_points_expiry_date) ? response.data.apc_points_expiry_date : '',
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
const getCustomerTier = (customerId) => {
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
        tier: hasValue(response.data.tier_code) ? response.data.tier_code : '',
      };
      return responseData;
    });
};

export {
  getCustomerInfo,
  getCustomerPoints,
  getCustomerTier,
};
