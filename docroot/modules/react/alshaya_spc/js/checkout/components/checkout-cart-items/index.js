import React from 'react';

import CheckoutCartItem from '../checkout-cart-item';

const CheckoutCartItems = (props) => {
  const { items } = props;
  const productItems = [];
  Object.entries(items).forEach(([key, product]) => {
    productItems.push(<CheckoutCartItem key={key} item={product} />);
  });

  return (
    <>
      {productItems}
    </>
  );
};

export default CheckoutCartItems;
