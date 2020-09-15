import React from 'react';

import CheckoutCartItem from '../checkout-cart-item';

const CheckoutCartItems = ({ items, context, couponCode }) => {
  const productItems = [];
  Object.entries(items).forEach(([key, product]) => {
    productItems.push(
      <CheckoutCartItem key={key} couponCode={couponCode} item={product} context={context} />,
    );
  });

  return (
    <>
      {productItems}
    </>
  );
};

export default CheckoutCartItems;
