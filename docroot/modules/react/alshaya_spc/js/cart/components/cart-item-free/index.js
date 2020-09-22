const CartItemFree = ({ freeItem }) => (
  (freeItem === true) ? Drupal.t('Free Gift with Purchase') : null
);

export default CartItemFree;
