import React from 'react';

const CartItemFree = ({ freeItem }) => ((freeItem) ? (
  <div className="freegift-label">{Drupal.t('Free Gift with Purchase')}</div>
) : null);

export default CartItemFree;
