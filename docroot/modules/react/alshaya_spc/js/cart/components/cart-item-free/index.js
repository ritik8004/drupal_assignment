import React from 'react';

const CartItemFree = ({ freeItem }) => (
  (freeItem === true) ? Drupal.t('Free gift with purchase') : null
);

export default React.memo(CartItemFree);
