import React from 'react';

const ProductLozenges = ({ labels, sku }) => {
  if (typeof labels === 'undefined' || labels.length === 0) {
    return (null);
  }
  const lozengesItems = labels.map(({ image, text }) => (
    <div className="label" key={image}>
      <img
        src={image}
        alt={text || ''}
        title={text || ''}
        loading="lazy"
      />
    </div>
  ));

  const labelPosition = (labels.length) ? labels[0].position : '';

  return (
    <div className={`labels-container product-lozenges-container ${labelPosition}`} data-type="spc-recommended-products" data-sku={sku} data-main-sku={sku}>
      {lozengesItems}
    </div>
  );
};

export default ProductLozenges;
