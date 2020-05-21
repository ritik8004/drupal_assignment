import React from 'react';

const PdpInfo = (props) => {
  const { title, pdpProductPrice, finalPrice } = props;
  let discountPercantage = null;
  if (!(pdpProductPrice === finalPrice)) {
    discountPercantage = ((pdpProductPrice - finalPrice) / pdpProductPrice) * 100;
  }

  return (
    <>
      <div className="pdp-info-wrapper">
        <div className="pdp-title-wrapper">{title}</div>
        <p>{pdpProductPrice}</p>
        <p>{finalPrice}</p>
        <div className="discount-percentage">{discountPercantage}</div>
      </div>
    </>
  );
};
export default PdpInfo;
