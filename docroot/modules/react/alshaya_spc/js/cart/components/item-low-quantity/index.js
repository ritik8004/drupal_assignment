const ItemLowQuantity = ({ in_stock: inStock, stock, qty }) => {
  if (inStock && stock < qty) {
    return Drupal.t('This product is not available in selected quantity. Please adjust the quantity to proceed.');
  }

  return null;
};

export default ItemLowQuantity;
