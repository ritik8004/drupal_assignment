const CartItemFree = (props) => {
  const { freeItem } = props;
  if (freeItem === true) {
    return Drupal.t('Free gift with purchase');
  }
  return null;
};

export default CartItemFree;
