import React from 'react';

import CartItem from '../cart-item';

const CartItems = (props) => {
  const { items, dynamicPromoLabelsProduct } = props;
  const productItems = [];

  // If more than one child sku of a common parent sku is added to cart
  // then the total qty of all chid sku in cart, should not exceed the
  // qty limit set for parent sku,therefore we are calculating the
  // total qty of added products in cart by parent sku.
  const qtyLimits = {};
  if (drupalSettings.quantity_limit_enabled) {
    Object.entries(items).forEach(([, product]) => {
      qtyLimits[product.parent_sku] = typeof qtyLimits[product.parent_sku] !== 'undefined'
        ? qtyLimits[product.parent_sku] + product.qty
        : product.qty;
    });
  }

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
        qtyLimit={qtyLimits[product.parent_sku] || 0}
        productPromotion={productPromotion}
      />,
    );
  });

  return (
    <div className="spc-cart-items">{productItems}</div>
  );
};

export default CartItems;
