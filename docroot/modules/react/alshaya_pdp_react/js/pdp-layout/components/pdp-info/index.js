import React from 'react';

const PdpInfo = (props) => {
  const { skuCode, pdpProductPrice, finalPrice } = props;
  const discountPercantage = ((pdpProductPrice - finalPrice) / pdpProductPrice) * 100;

  return (
    <>
      <div className="pdp-title-wrapper">{skuCode}</div>
      <div className="pdp-discount">{discountPercantage}</div>
    </>
  );
};
export default PdpInfo;
