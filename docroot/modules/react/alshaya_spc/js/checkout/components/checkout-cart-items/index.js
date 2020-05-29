import React from 'react';

import CheckoutCartItem from '../checkout-cart-item';

const CheckoutCartItems = (props) => {
  const { items, context } = props;
  const productItems = [];
  Object.entries(items).forEach(([key, product]) => {
    productItems.push(<CheckoutCartItem key={key} item={product} context={context} />);
  });

  return (
    <>
      {productItems}
    </>
  );
};

export default CheckoutCartItems;
