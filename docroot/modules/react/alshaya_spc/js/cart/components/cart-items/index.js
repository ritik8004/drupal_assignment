import React from 'react';

import CartItem from '../cart-item';

const CartItems = (props) => {
  const { items } = props;

  const productItems = [];
  Object.entries(items).forEach(([key, product], index) => {
    const animationOffset = (1 + index) / 5;
    productItems.push(<CartItem animationOffset={animationOffset} key={key} item={product} />);
  });

  return (
    <div className="spc-cart-items">{productItems}</div>
  );
};

export default CartItems;
