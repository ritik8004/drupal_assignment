import React from 'react';
import ReturnOrderSummary from '../return-order-summary';
import ReturnItemsListing from '../return-items-listing';
import { getOrderDetailsForReturnRequest } from '../../../utilities/return_request_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const ReturnRequest = () => {
  const orderDetails = getOrderDetailsForReturnRequest();
  if (!hasValue(orderDetails)) {
    return null;
  }
  return (
    <div className="return-requests-wrapper">
      <ReturnOrderSummary
        orderDetails={orderDetails}
      />
      <ReturnItemsListing
        products={orderDetails['#products']}
      />
    </div>
  );
};

export default ReturnRequest;
