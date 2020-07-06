import React from 'react';

const CheckoutConfigurableOption = (props) => {
  const { label } = props;

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
