import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const ProductTeaserAttributes = ({ plpProductCategoryAttributes }) => {
  if (!hasValue(plpProductCategoryAttributes)) {
    return null;
  }
  // Display teaser attributes, if attribute is an array
  // then show it as comma separated values.
  // eg. gender - Men's
  // product_type - [Sport Lifestyle, Suit]
  // It will be rendered as
  // Men's Sport Lifestyle, Suit
  let attributes = '';
  Object.keys(plpProductCategoryAttributes).forEach((key) => {
    let attrValue = plpProductCategoryAttributes[key];
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
