import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import logger from '../../../../js/utilities/logger';
import { getErrorResponse } from '../../../../js/utilities/error';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

/**
 * Get Customer Information.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
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
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
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
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
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

/**
 * Get Customer Progress Tracker.
 *
 * @returns {Object}
 *   Progress tracker data.
 */
const prepareProgressTrackerResponse = (progressTracker) => ({
  nextTierLevel: hasValue(progressTracker.tier_code) ? progressTracker.tier_code : '',
  userPoints: hasValue(progressTracker.current_value) ? progressTracker.current_value : '',
  nextTierThreshold: hasValue(progressTracker.max_value) ? progressTracker.max_value : '',
});

/**
 * Get Customer Progress Tracker.
 *
 * @param {string} customerId
 *   The customer Id.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
 */
const getCustomerProgressTracker = (customerId) => {
  const endpoint = `/V1/customers/apcTierProgressData/customerId/${customerId}`;

  return callMagentoApi(endpoint, 'GET')
    .then((response) => {
      if (hasValue(response.data.error)) {
        logger.error('Error while trying to get progress tracker of the user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': response.data.error_message || '',
        });
        return getErrorResponse(response.data.error_message, response.data.error_code);
      }

      let trackerData = {};
      if (response.data.tier_progress_tracker.length > 0) {
        response.data.tier_progress_tracker.forEach((progressTracker) => {
          if (progressTracker.code.includes('UPG')) {
            trackerData = prepareProgressTrackerResponse(progressTracker);
            return;
          }
          trackerData = prepareProgressTrackerResponse(progressTracker);
        });
      }
      return trackerData;
    });
};

export {
  getCustomerInfo,
  getCustomerPoints,
  getCustomerTier,
  getCustomerProgressTracker,
};
