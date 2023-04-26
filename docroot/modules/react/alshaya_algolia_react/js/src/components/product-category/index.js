import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const ProductCategory = ({
  showProductCategory,
  genderText,
  productType,
}) => {
  if (!showProductCategory
    || (!hasValue(genderText[0]) && !hasValue(productType[0]))
  ) {
    return null;
  }

  return (
    <div className="gender-text">
      <span className="categories">
        {hasValue(genderText[0]) ? genderText[0] : ''}
        {' '}
        {hasValue(productType[0]) ? productType[0] : ''}
      </span>
    </div>
  );
};

export default ProductCategory;
