const CartItemOOS = (props) => {
  const { inStock } = props;
  if (inStock !== true) {
    return Drupal.t('This product is out of stock. Please remove to proceed.');
  }
  return null;
};

export default CartItemOOS;
