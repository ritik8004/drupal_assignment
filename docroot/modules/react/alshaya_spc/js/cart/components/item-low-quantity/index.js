import React from 'react';

const ItemLowQuantity = (props) => {
  const {
    in_stock: inStock,
    stock,
    qty,
  } = props;
  if (inStock && stock < qty) {
    return <div>{Drupal.t('This product is not available in selected quantity. Please adjust the quantity to proceed.')}</div>;
  }

  return (null);
};

export default ItemLowQuantity;
