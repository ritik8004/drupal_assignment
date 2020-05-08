import React from 'react';

const PdpInfo = (props) => {
  const { skuCode, pdpProductPrice, finalPrice } = props;
  let discountPercantage = null;
  if (!(pdpProductPrice === finalPrice)) {
    discountPercantage = ((pdpProductPrice - finalPrice) / pdpProductPrice) * 100;
  }

  return (
    <>
      <div className="pdp-title-wrapper">{skuCode}</div>
      <div className="discount-percentage">{discountPercantage}</div>
    </>
  );
};
export default PdpInfo;
