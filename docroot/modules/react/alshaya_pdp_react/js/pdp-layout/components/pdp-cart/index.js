import React, { useRef, useEffect } from 'react';
import ConfigurableProductForm from './configurable-product-form';
import SimpleProductForm from './simple-product-form';

const PdpCart = ({
  configurableCombinations,
  skuCode,
  productInfo,
  pdpRefresh,
  childRef,
  pdpLabelRefresh,
  stockQty,
  firstChild,
}) => {
  const wrapper = useRef();

  useEffect(() => {
    if (childRef) {
      childRef(wrapper);
    }
  },
  []);

  if (configurableCombinations) {
    return (
      <div className="pdp-cart-form fadeInUp notInMobile" style={{ animationDelay: '0.6s' }} ref={wrapper}>
        <ConfigurableProductForm
          configurableCombinations={configurableCombinations}
          skuCode={skuCode}
          productInfo={productInfo}
          pdpRefresh={pdpRefresh}
          pdpLabelRefresh={pdpLabelRefresh}
          stockQty={stockQty}
          firstChild={firstChild}
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
        stockQty={stockQty}
      />
    </div>
  );
};

export default PdpCart;
