import React from 'react';
import ConfigurableProductForm from './configurable-product-form';
import SimpleProductForm from './simple-product-form';

const PdpCart = (props) => {
  const {
    configurableCombinations, skuCode, productInfo, pdpRefresh,
  } = props;

  if (configurableCombinations) {
    return (
      <div className="pdp-cart-form">
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
