import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const QuickFitAttribute = ({ productInfo, skuCode }) => {
  if (hasValue(productInfo[skuCode].quickFit)) {
    return (
      <div className="product-quickfit">
        { productInfo[skuCode].quickFit }
      </div>
    );
  }
  return (
    <></>
  );
};

export default QuickFitAttribute;
