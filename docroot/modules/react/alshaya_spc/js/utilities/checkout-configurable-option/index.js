import React from 'react';

const CheckoutConfigurableOption = (props) => {
  const { label } = props;

  // Hiding LPN attribute data from product summary
  // as per requirement: CORE-22708.
  if (drupalSettings.lpn !== undefined
    && drupalSettings.lpn.lpn_attribute !== ''
    && drupalSettings.lpn.lpn_attribute === label.attribute_id) {
    return null;
  }

  // The attributes eg: subset_name are configurable but not shown on cart
  // these are passed with null value and will not be shown.
  if (Object.prototype.hasOwnProperty.call(label, 'value') && label.value === null) {
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
