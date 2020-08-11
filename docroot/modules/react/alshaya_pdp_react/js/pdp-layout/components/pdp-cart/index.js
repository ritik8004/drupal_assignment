import React from 'react';
import ConfigurableProductForm from './configurable-product-form';
import SimpleProductForm from './simple-product-form';

const PdpCart = ({
  configurableCombinations, skuCode, productInfo, pdpRefresh, childRef, pdpLabelRefresh,
}) => {
  if (configurableCombinations) {
    return (
      <div className="pdp-cart-form fadeInUp notInMobile" style={{ animationDelay: '0.6s' }} ref={childRef}>
        <ConfigurableProductForm
          configurableCombinations={configurableCombinations}
          skuCode={skuCode}
          productInfo={productInfo}
          pdpRefresh={pdpRefresh}
          pdpLabelRefresh={pdpLabelRefresh}
        />
      </div>
    );
  }
  return (
    <div className="pdp-cart-form fadeInUp notInMobile" style={{ animationDelay: '0.6s' }}>
      <SimpleProductForm
        skuCode={skuCode}
        productInfo={productInfo}
        pdpLabelRefresh={pdpLabelRefresh}
      />
    </div>
  );
};

export default PdpCart;
