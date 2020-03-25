import React from 'react';

const CartItemOOS = (props) => {
  const { inStock } = props;
  if (inStock !== true) {
    return <div className="spc-cart-item-warning">{Drupal.t('This product is out of stock. Please remove to proceed.')}</div>;
  }
  return null;
};
export default CartItemOOS;
