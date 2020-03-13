import React from 'react';

import CartItem from '../cart-item';

const CartItems = (props) => {
  const { items } = props;

  const productItems = [];
  Object.entries(items).forEach(([key, product]) => {
    productItems.push(<CartItem key={key} item={product} />);
  });

  return (
    <div className="spc-cart-items">{productItems}</div>
  );
};

export default CartItems;
