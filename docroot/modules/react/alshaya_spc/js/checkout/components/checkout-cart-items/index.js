import React from 'react';

import CheckoutCartItem from '../checkout-cart-item';
import CheckoutVirtualCartItem from '../../../egift-card/components/egift-virtual-checkout-cart-item';

const CheckoutCartItems = ({ items, context, couponCode }) => {
  const productItems = [];
  Object.entries(items).forEach(([key, product]) => {
    if (product.isEgiftCard) {
      // Virtual product component for egift card.
      productItems.push(
        <CheckoutVirtualCartItem key={key} item={product} />,
      );
    } else {
      // Normal products.
      productItems.push(
        <CheckoutCartItem key={key} couponCode={couponCode} item={product} context={context} />,
      );
    }
  });

  return (
    <>
      {productItems}
    </>
  );
};

export default CheckoutCartItems;
