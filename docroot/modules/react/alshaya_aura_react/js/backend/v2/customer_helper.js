import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import logger from '../../../../js/utilities/logger';
import { getErrorResponse } from '../../../../js/utilities/error';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { isUserAuthenticated } from '../../../../js/utilities/helper';

/**
 * Prepare data for aura user status update.
 *
 * @param {Object} data
 *   The data to process.
 *
 * @returns {Object}
 *   Error in case of missing data else the processed data.
 */
const prepareAuraUserStatusUpdateData = (data) => {
  if (!hasValue(data.uid) || !hasValue(data.apcIdentifierId) || !hasValue(data.link)) {
    logger.error('Error while trying to prepare data for updating user AURA Status. User Id, AURA Card number and Link value is required. Data: @request_data', {
      '@request_data': JSON.stringify(data),
    });
    return getErrorResponse('User Id, AURA Card number and Link value is required.', 404);
  }

  const processedData = {
    statusUpdate: {
      apcIdentifierId: data.apcIdentifierId,
      link: data.link,
    },
  };

  if (hasValue(data.type) && data.type === 'withOtp') {
    if (!hasValue(data.otp) || !hasValue(data.phoneNumber)) {
      logger.error('Error while trying to prepare data for updating user AURA Status. OTP and mobile number is required. Data: @request_data', {
        '@request_data': JSON.stringify(data),
      });
      return getErrorResponse('OTP and mobile number is required.', 404);
    }

    processedData.statusUpdate.otp = data.otp;
    processedData.statusUpdate.phoneNumber = data.phoneNumber.replace('+', '');
  }

  return processedData;
};

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
        let responseData = null;
        // If we have error but response status in 200 then we assume data doesn't exist.
        if (response.status === 200) {
          responseData = {
            auraStatus: 0,
          };
          return responseData;
        }

        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch customer information for user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': message,
        });
        return getErrorResponse(message, 500);
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
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch loyalty points for user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': message,
        });
        return getErrorResponse(message, 500);
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
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch tier information for user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': message,
        });
        return getErrorResponse(message, 500);
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

/**
 * Sets loyalty card in cart.
 *
 * @param {string} identifierNo
 *   Identifier number.
 * @param {string} quoteId
 *   Quote/Cart ID.
 *
 * @returns {Promise}
 *   Promise that resolves to an object which contains the status true/false or
 * the error object.
 */
const setLoyaltyCard = (identifierNo, quoteId) => {
  let endpoint = '/V1/apc/set-loyalty-card';
  let data = { quote_id: quoteId, identifier_no: identifierNo };

  if (isUserAuthenticated()) {
    endpoint = '/V1/customers/mine/set-loyalty-card';
    data = { identifier_no: identifierNo };
  }

  return callMagentoApi(endpoint, 'POST', data).then((response) => {
    if (hasValue(response.data.error)) {
      logger.notice('Error while trying to set loyalty card in cart. Backend error. Request Data: @data. Message: @message', {
        '@data': JSON.stringify(data),
        '@message': response.data.error_message,
      });
      return response.data;
    }

    return {
      status: response,
    };
  });
};

/**
 * Get Customer Reward Activity.
 *
 * @param {string} customerId
 *   The customer Id.
 * @param {string} fromDate
 *   From date.
 * @param {string} toDate
 *   To date.
 * @param {string} maxResults
 *   Max result to fetch.
 * @param {string} channel
 *   Online(K)/ InStore(V).
 * @param {string} partnerCode
 *   The brand code.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing customer data in case of
 *   success or an error object in case of failure.
 */
const getCustomerRewardActivity = (
  customerId,
  fromDate,
  toDate,
  maxResults,
  channel,
  partnerCode,
) => {
  // We are always passing `orderField=date:DESC`.
  const endpoint = `/V1/customers/apcTransactions?customerId=${customerId}&fromDate=${fromDate}&toDate=${toDate}&orderField=date:DESC&maxResults=${maxResults}&channel=${channel}&partnerCode=${partnerCode}`;

  return callMagentoApi(endpoint, 'GET')
    .then((response) => {
      if (hasValue(response.data.error)) {
        logger.error('Error while trying to get reward activity of the user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': response.data.error_message || '',
        });
        return getErrorResponse(response.data.error_message, response.data.error_code);
      }

      const transactions = [];
      if (hasValue(response.data.apc_transactions)) {
        response.data.apc_transactions.forEach((transaction) => {
          const transactionData = {
            orderNo: transaction.trn_no,
            date: transaction.date,
            orderTotal: transaction.total_value,
            currencyCode: transaction.currency_code,
            channel: transaction.channel,
            auraPoints: transaction.points,
            brandName: transaction.location_name,
          };

          const pointBalances = hasValue(transaction.points_balances)
            ? transaction.points_balances.shift()
            : [];
          if (hasValue(pointBalances)) {
            transactionData.status = pointBalances.status;
            transactionData.statusName = pointBalances.status_name;
          }

          transactions.push(transactionData);
        });
      }
      return transactions;
    });
};

export {
  prepareAuraUserStatusUpdateData,
  getCustomerInfo,
  getCustomerPoints,
  getCustomerTier,
  getCustomerProgressTracker,
  setLoyaltyCard,
  getCustomerRewardActivity,
};
