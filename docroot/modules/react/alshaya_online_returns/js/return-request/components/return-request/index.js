import React from 'react';
import ReturnOrderSummary from '../return-order-summary';
import ReturnItemsListing from '../return-items-listing';
import { getOrderDetailsForReturnRequest } from '../../../utilities/return_request_util';

const ReturnRequest = () => {
  const orderDetails = getOrderDetailsForReturnRequest();
  if (orderDetails === null) {
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
