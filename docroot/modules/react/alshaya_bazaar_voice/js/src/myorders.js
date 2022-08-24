import React from 'react';
import ReactDOM from 'react-dom';
import RecentOrders from './myaccount/components/orders/recent-orders';

document.addEventListener('onRecentOrderView', (orderDetails) => {
  // Do not re-process.
  if (orderDetails.detail.data.context.classList.contains('processed')) {
    return;
  }
  orderDetails.detail.data.context.classList.add('processed');

  const reviewButton = orderDetails.detail.data.context.querySelectorAll('.myaccount-write-review');
  if (reviewButton !== null) {
    Array.from(reviewButton).forEach((product) => {
      ReactDOM.render(
        <RecentOrders
          productId={product.getAttribute('data-sku')}
          productVariantId={product.getAttribute('data-variant-sku')}
        />, product,
      );
    });
  }
});
