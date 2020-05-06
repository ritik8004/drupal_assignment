import React from 'react';

const PdpInfo = (props) => {
  const { skuCode } = props;

  return (
    <>
      <div className="pdp-title-wrapper">{skuCode}</div>
    </>
  );
};
export default PdpInfo;
