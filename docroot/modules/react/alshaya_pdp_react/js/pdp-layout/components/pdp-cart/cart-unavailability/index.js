import React from 'react';

const CartUnavailability = () => (
  <p className="not-buyable-message">{Drupal.t('Add to bag is temporarily unavailable')}</p>
);

export default CartUnavailability;
