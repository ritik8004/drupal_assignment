import { hasValue } from '../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../js/utilities/error';
import logger from '../../../js/utilities/logger';
import { callMagentoApi, prepareFilterData } from '../../../js/utilities/requestHelper';
import { getOrderDetails } from './online_returns_util';

/**
 * Prepare data to create return request.
 *
 * @param {Object} data
 *   The data to process.
 *
 * @returns {Object}
 *   Error in case of missing data else the processed data.
 */
const prepareReturnRequestData = async (data) => {
  if (!hasValue(data)) {
    logger.error('Error while trying to prepare data for creating return request. Data: @request_data', {
      '@request_data': JSON.stringify(data),
    });
    return getErrorResponse('Request data is required.', 404);
  }

  const orderDetails = await getOrderDetails();

  // Process request data in required format.
  const items = [];
  let orderId = '';
  let storeId = '';
  const status = 'pending';

  data.forEach((product) => {
    orderId = product.order_id;
    storeId = product.store_id;

    items.push(
      {
        order_item_id: product.item_id,
        qty_requested: product.qty_requested,
        resolution: product.resolution,
        reason: product.reason,
        status,
      },
    );
  });

  const processedData = {
    rmaDataObject: {
      order_id: orderId,
      order_increment_id: orderDetails['#order'].orderId,
      store_id: storeId,
      status,
      items,
    },
  };

  return processedData;
};

const createReturnRequest = async (itemsSelected,
  egiftCardType,
  cardNumber,
  isEgiftSelected,
  isHybrid) => {
  const data = await prepareReturnRequestData(itemsSelected);

  if (hasValue(data.error)) {
    logger.error('Error while trying to prepare return request data. Data: @data.', {
      '@data': JSON.stringify(data),
    });
    return { data };
  }

  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to create a return request. No user available in session. User id: @user_id. Customer id: @customer_id.', {
      '@user_id': uid,
      '@customer_id': customerId,
    });
    return getErrorResponse('No user available in session', 403);
  }

  if (egiftCardType && isEgiftSelected && !isHybrid) {
    // For users for whom new eGift card needs to be generated.
    data.rmaDataObject.extension_attributes = {
      refund_mode: 'hps_payment',
      egift_card_type: 'new',
      link_egift_card: 1,
    };
  } else if (!egiftCardType && hasValue(cardNumber) && isEgiftSelected && !isHybrid) {
    // For users having linked eGift card.
    data.rmaDataObject.extension_attributes = {
      refund_mode: 'hps_payment',
      egift_card_type: 'linked',
      egift_card_number: cardNumber,
    };
  }

  data.rmaDataObject.customer_id = customerId;

  return callMagentoApi('/V1/rma/returns', 'POST', data).then((response) => {
    if (hasValue(response.data.error)) {
      logger.notice('Error while trying to create a return request. Request Data: @data. Message: @message', {
        '@data': JSON.stringify(data),
        '@message': response.data.error_message,
      });
      return response.data;
    }

    return response;
  });
};

/**
 * Get Return details.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing return data in case of
 *   success or an error object in case of failure.
 */
const getReturnInfo = (returnId) => {
  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to get return info. No user available in session. User id: @user_id. Customer id: @customer_id.', {
      '@user_id': uid,
      '@customer_id': customerId,
    });
    return getErrorResponse('No user available in session', 403);
  }

  const endpoint = `/V1/rma/returns/${returnId}`;

  return callMagentoApi(endpoint, 'GET')
    .then((response) => {
      if (hasValue(response.data.error)) {
        const message = hasValue(response.data.message) ? response.data.message : '';
        logger.error('Error while trying to fetch return information for user with customer id @customerId. Endpoint: @endpoint. Message: @message', {
          '@customerId': customerId,
          '@endpoint': endpoint,
          '@message': message,
        });
        return getErrorResponse(message, 500);
      }
      return response;
    });
};

/**
 * Get Return details by order id.
 *
 * @returns {Promise}
 *   Promise that resolves to an object containing return data in case of
 *   success or an error object in case of failure.
 */
const getReturnsByOrderId = async (orderId) => {
  if (!hasValue(orderId)) {
    logger.error('Order Id is required to get returns for the order.');
    return getErrorResponse('Order Id is required to get returns for the order.', 400);
  }

  const filters = {
    field: 'order_id',
    value: orderId,
  };
  const preparedFilterData = prepareFilterData([filters]);

  return callMagentoApi('/V1/rma/returns', 'GET', preparedFilterData).then((response) => {
    if (hasValue(response.data.error)) {
      logger.notice('Error while trying to get returns by order id. Request Data: @data. Message: @message', {
        '@data': JSON.stringify(preparedFilterData),
        '@message': response.data.error_message,
      });
      return response.data;
    }

    return response;
  });
};

/**
 * Utility function to check if return is open or not.
 *
 * @param {object} returnItem
 *   The individual return item object.
 *
 * @returns {boolean}
 *   True if order return if closed else False.
 */
function isReturnClosed(returnItem) {
  return returnItem.extension_attributes.is_closed;
}

/**
 * Utility function to check if return is picked or not.
 *
 * @param {object} returnItem
 *   The individual return item object.
 *
 * @returns {boolean}
 *   True if order return if picked else False.
 */
function isReturnPicked(returnItem) {
  return returnItem.extension_attributes.is_picked;
}

/**
 * Utility function to validate if return request is valid.
 */
async function validateReturnRequest(orderDetails) {
  const returnItems = await getReturnsByOrderId(orderDetails['#order'].orderEntityId);

  // Return false if the api results in some error.
  if (hasValue(returnItems.error)) {
    return false;
  }

  // Validate if all the items are refunded or cancelled.
  let totalProductQty = 0;
  let totalRefundedQty = 0;
  orderDetails['#products'].forEach((item) => {
    totalProductQty += item.actual_ordered;
    totalRefundedQty += item.refunded;
  });

  if (totalProductQty > 0
    && totalRefundedQty > 0
    && totalProductQty === totalRefundedQty) {
    return false;
  }

  // Return false if any active return already exists for same order.
  if (hasValue(returnItems.data.items)) {
    if (returnItems.data.items.some((item) => isReturnClosed(item) === false)) {
      logger.notice('Error while trying to create return request. Return request already raised for Order: @orderId', {
        '@data': JSON.stringify(returnItems.data),
        '@orderId': orderDetails['#order'].orderId,
      });
      return false;
    }
  }

  // Return false if current order is not eligible for return.
  if (!(orderDetails['#order'].isReturnEligible)) {
    logger.notice('Error while trying to create return request. Order: @orderId is not eligible for return', {
      '@orderId': orderDetails['#order'].orderId,
    });
    return false;
  }

  return true;
}

/**
 * Utility function to prepare request data for cancel return api.
 */
function prepareCancelRequestData(returnInfo) {
  // Process request data in required format.
  const items = [];
  const status = 'closed';

  returnInfo.items.forEach((item) => {
    items.push(
      {
        entity_id: item.entity_id,
        rma_entity_id: item.rma_entity_id,
        status,
        order_item_id: item.order_item_id,
      },
    );
  });

  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;

  // Check if we have user in session.
  if (!hasValue(customerId)) {
    logger.error('Error while trying to cancel a return request. No user available in session. Customer id: @customer_id.', {
      '@customer_id': customerId,
    });
    return getErrorResponse('No user available in session', 403);
  }

  const requestData = {
    rmaDataObject: {
      customer_id: customerId,
      increment_id: returnInfo.increment_id,
      entity_id: returnInfo.entity_id,
      order_id: returnInfo.order_id,
      status,
      items,
    },
  };
  return requestData;
}

/**
 * Utility function to call api for cancel return request.
 */
const cancelReturnRequest = async (returnInfo) => {
  if (!hasValue(returnInfo)) {
    logger.error('Error while trying to prepare data for cancel return request. Data: @request_data', {
      '@request_data': JSON.stringify(returnInfo),
    });
    return getErrorResponse('Request data is required.', 404);
  }
  const data = prepareCancelRequestData(returnInfo);

  const endpoint = `/V1/rma/returns/${returnInfo.entity_id}`;

  return callMagentoApi(endpoint, 'PUT', data).then((response) => {
    if (hasValue(response.data.error)) {
      logger.notice('Error while trying to cancel a return request. Request Data: @data. Message: @message', {
        '@data': JSON.stringify(data),
        '@message': response.data.error_message,
      });
      return response.data;
    }

    return response;
  });
};

export {
  createReturnRequest,
  prepareReturnRequestData,
  getReturnInfo,
  getReturnsByOrderId,
  validateReturnRequest,
  prepareCancelRequestData,
  cancelReturnRequest,
  isReturnClosed,
  isReturnPicked,
};
