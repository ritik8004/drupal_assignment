import React from 'react';

const ProductTeaserAttributes = ({ productTeaserAttributes }) => {
  // Display teaser attributes, if attribute is an array
  // then show it as comma separated values.
  let attributes = '';
  Object.keys(productTeaserAttributes).forEach((key) => {
    let attrValue = productTeaserAttributes[key];
    attrValue = Array.isArray(attrValue)
      ? attrValue.join(', ')
      : attrValue;
    attributes += `${attrValue} `;
  });

  return (
    <div className="product-teaser-attributes-wrapper">
      <span className="product-teaser-attributes">
        {attributes}
      </span>
    </div>
  );
};

export default ProductTeaserAttributes;
