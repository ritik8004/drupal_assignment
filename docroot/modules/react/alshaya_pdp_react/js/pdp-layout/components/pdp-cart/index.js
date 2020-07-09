import React, { useRef, useEffect } from 'react';
import ConfigurableProductForm from './configurable-product-form';
import SimpleProductForm from './simple-product-form';

const PdpCart = (props) => {
  const {
    configurableCombinations, skuCode, productInfo, pdpRefresh,
    childRef,
  } = props;

  const wrapper = useRef();

  useEffect(() => {
    if (childRef) {
      childRef(wrapper);
    }
  },
  [
    childRef,
    wrapper,
  ]);

  if (configurableCombinations) {
    return (
      <div className="pdp-cart-form" ref={wrapper}>
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
