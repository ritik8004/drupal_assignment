import React from 'react';

const CartItemOOS = ({ inStock }) => (
  (inStock !== true) ? Drupal.t('This product is out of stock. Please remove to proceed.') : null
);

export default React.memo(CartItemOOS);
