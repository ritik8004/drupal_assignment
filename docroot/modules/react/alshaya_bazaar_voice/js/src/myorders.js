import React from 'react';
import ReactDOM from 'react-dom';
import RecentOrders from './myaccount/components/orders/recent-orders';

const productElements = document.getElementsByClassName('myaccount-write-review');
if (productElements.length > 0) {
  Array.from(productElements).forEach((product) => {
    ReactDOM.render(
      <RecentOrders
        productId={product.getAttribute('data-sku')}
      />, product,
    );
  });
}
