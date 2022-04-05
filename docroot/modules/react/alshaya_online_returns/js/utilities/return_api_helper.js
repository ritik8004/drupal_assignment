import { hasValue } from '../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../js/utilities/error';
import logger from '../../../js/utilities/logger';
import { callMagentoApi } from '../../../js/utilities/requestHelper';
import { getOrderDetailsForReturnRequest } from './return_request_util';

/**
 * Prepare data to create return request.
 *
 * @param {Object} data
 *   The data to process.
 *
 * @returns {Object}
 *   Error in case of missing data else the processed data.
 */
const prepareReturnRequestData = (data) => {
  if (!hasValue(data)) {
    logger.error('Error while trying to prepare data for creating return request. Data: @request_data', {
      '@request_data': JSON.stringify(data),
    });
    return getErrorResponse('Request data is required.', 404);
  }

  const orderDetails = getOrderDetailsForReturnRequest();

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

const createReturnRequest = async (rawData) => {
  const data = prepareReturnRequestData(rawData.itemsSelected);

  if (hasValue(data.error)) {
    logger.error('Error while trying to prepare return request data. Data: @data.', {
      '@data': JSON.stringify(data),
    });
    return { data };
  }

  const { customerId } = drupalSettings.userDetails;
  // Get user details from session.
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to create a return request. No user available in session. User id from request: @uid.', {
      '@uid': rawData.uid,
    });
    return getErrorResponse('No user available in session', 404);
  }

  // Check if uid in the request matches the one in session.
  if (uid !== rawData.uid) {
    logger.error('Error while trying to create a return request. User id in request doesn\'t match the one in session. User id from request: @req_uid. User id in session: @session_uid.', {
      '@req_uid': rawData.uid,
      '@session_uid': uid,
    });
    return getErrorResponse("User id in request doesn't match the one in session.", 404);
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

    return {
      status: true,
      data: response,
    };
  });
};

export {
  createReturnRequest,
  prepareReturnRequestData,
};
