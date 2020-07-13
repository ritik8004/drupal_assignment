import React from 'react';
import ConfigurableProductForm from './configurable-product-form';
import SimpleProductForm from './simple-product-form';

const PdpCart = ({
  configurableCombinations, skuCode, productInfo, pdpRefresh, childRef,
}) => {
  if (configurableCombinations) {
    return (
      <div className="pdp-cart-form" ref={childRef}>
        <ConfigurableProductForm
          configurableCombinations={configurableCombinations}
          skuCode={skuCode}
          productInfo={productInfo}
          pdpRefresh={pdpRefresh}
        />
      </div>
    );
  }
  return (
    <div className="pdp-cart-form">
      <SimpleProductForm
        skuCode={skuCode}
        productInfo={productInfo}
      />
    </div>
  );
};

export default PdpCart;
