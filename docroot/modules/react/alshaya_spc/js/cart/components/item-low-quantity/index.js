import React from 'react';

const ItemLowQuantity = (props) => {
  if (props.in_stock && props.stock < props.qty) {
    return <div>{Drupal.t('This product is not available in selected quantity. Please adjust the quantity to proceed.')}</div>;
  }

  return (null);
};

export default ItemLowQuantity;
