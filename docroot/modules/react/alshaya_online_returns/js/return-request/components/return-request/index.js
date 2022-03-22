import React from 'react';
import ReturnOrderSummary from '../return-order-summary';
import ReturnItemsListing from '../return-items-listing';

const ReturnRequest = () => {
  const { orderDetails } = drupalSettings;
  if (!orderDetails) {
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
