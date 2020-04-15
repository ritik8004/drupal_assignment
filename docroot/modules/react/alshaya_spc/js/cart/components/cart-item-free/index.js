const CartItemFree = ({ freeItem }) => (
  (freeItem === true) ? Drupal.t('Free gift with purchase') : null
);

export default CartItemFree;
