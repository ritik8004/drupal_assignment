import React from 'react';

const ProductLozenges = ({ labels, sku }) => {
  if (typeof labels === 'undefined' || labels.length === 0) {
    return (null);
  }
  const lozengesItems = labels.map(({ image, position, text }) => (
    <div className={`label ${position}`} key={image}>
      <img
        src={image}
        alt={text || ''}
        title={text || ''}
        loading="lazy"
      />
    </div>
  ));

  return (
    <div className="labels-container product-lozenges-container" data-type="spc-recommended-products" data-sku={sku} data-main-sku={sku}>
      {lozengesItems}
    </div>
  );
};

export default ProductLozenges;
