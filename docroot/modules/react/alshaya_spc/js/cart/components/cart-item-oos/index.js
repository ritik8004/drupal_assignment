import React from 'react';

const CartItemOOS = (props) => {
  const { inStock } = props;
  if (inStock !== true) {
    return <div>{Drupal.t('This product is out of stock. Please remove to proceed.')}</div>;
  }
  return null;
};
export default CartItemOOS;
