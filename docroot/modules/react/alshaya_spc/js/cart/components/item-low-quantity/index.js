const ItemLowQuantity = ({
  in_stock: inStock, stock, qty,
}) => (
  (inStock && stock < qty)
    ? Drupal.t('This product is not available in selected quantity. Please adjust the quantity to proceed.')
    : null
);

export default ItemLowQuantity;
