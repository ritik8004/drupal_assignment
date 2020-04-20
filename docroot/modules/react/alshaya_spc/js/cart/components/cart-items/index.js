import React from 'react';

import CartItem from '../cart-item';

const CartItems = (props) => {
  const { items, dynamicPromoLabelsProduct } = props;
  const productItems = [];
  Object.entries(items).forEach(([key, product], index) => {
    let productPromotion = false;
    if (dynamicPromoLabelsProduct !== null && key in dynamicPromoLabelsProduct) {
      productPromotion = dynamicPromoLabelsProduct[key];
    }
    const animationOffset = (1 + index) / 5;
    productItems.push(
      <CartItem
        animationOffset={animationOffset}
        key={key}
        item={product}
        productPromotion={productPromotion}
      />,
    );
  });

  return (
    <div className="spc-cart-items">{productItems}</div>
  );
};

export default CartItems;
