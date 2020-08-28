import React from 'react';

const CheckoutConfigurableOption = (props) => {
  const { label } = props;

  if (drupalSettings.lpn !== undefined
    && drupalSettings.lpn.lpn_attribute !== ''
    && drupalSettings.lpn.lpn_attribute === label.attribute_id) {
    return null;
  }

  return (
    <>
      <div className="spc-cart-product-attribute">
        <span className="spc-cart-product-attribute-label">{`${label.label}: `}</span>
        <span className="spc-cart-product-attribute-value">{label.value}</span>
      </div>
    </>
  );
};

export default CheckoutConfigurableOption;
